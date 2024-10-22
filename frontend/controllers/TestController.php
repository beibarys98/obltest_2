<?php

namespace frontend\controllers;

use common\models\Admin;
use common\models\Answer;
use common\models\File;
use common\models\Question;
use common\models\Teacher;
use common\models\TeacherAnswer;
use common\models\TeacherResult;
use common\models\TeacherSearch;
use common\models\TempFile;
use common\models\Test;
use common\models\TestResult;
use common\models\TestSearch;
use DOMDocument;
use DOMXPath;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\ZipArchive;
use Smalot\PdfParser\Parser;
use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

class TestController extends Controller
{
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    public function actionIndex()
    {
        $searchModel = new TestSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        $test = Test::findOne($id);
        $questions = Question::find()
            ->andWhere(['test_id' => $id])
            ->all();

        return $this->render('view', [
            'test' => $test,
            'questions' => $questions,
        ]);
    }

    public function actionCreate()
    {
        $model = new Test();

        if ($this->request->isPost) {

            $model->file = UploadedFile::getInstance($model, 'file');

            if ($model->load($this->request->post())) {

                if ($model->file) {
                    $datePath = date('y/m/d');
                    $directoryPath = 'tests/' . $datePath;
                    if (!is_dir($directoryPath)) {
                        mkdir($directoryPath, 0755, true);
                    }
                    $filePath = $directoryPath . '/'
                        . $model->file->baseName . '_'
                        . date('H_i_s') . '.'
                        . $model->file->extension;

                    if ($model->file->saveAs($filePath)) {
                        $model->path = $filePath;
                    }
                }

                $model->status = 'new';
                $model->save(false);

                $linesArray = $this->parseWordDocument($filePath);
                $this->processAndStoreQuestions($linesArray, $model->id);

                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    function parseWordDocument($filePath)
    {
        $newFilePath = $this->ignoreFormula($filePath);

        $phpWord = IOFactory::load($newFilePath);
        $lines = [];

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if ($element instanceof TextRun) {
                    $textLine = '';

                    // Process each element within the TextRun
                    foreach ($element->getElements() as $textElement) {
                        if ($textElement instanceof Text) {
                            $textLine .= $textElement->getText();
                        }
                    }

                    $lines[] = [
                        'text' => $textLine,
                    ];
                }
            }
        }

        return $lines;
    }

    public function ignoreFormula($filePath){
        $zip = new ZipArchive;
        if ($zip->open($filePath) === TRUE) {
            $xmlContent = '';
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entry = $zip->getNameIndex($i);
                if (strpos($entry, 'word/document.xml') !== false) {
                    $xmlContent = $zip->getFromIndex($i);
                    break;
                }
            }
            $zip->close();
        }

        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadXML($xmlContent);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('m', 'http://schemas.openxmlformats.org/officeDocument/2006/math');

        $nodes = $xpath->query('//m:*');
        foreach ($nodes as $node) {
            $node->parentNode->removeChild($node);
        }

        $modifiedXmlContent = $dom->saveXML();

        $datePath = date('y/m/d');
        $directoryPath = 'tests/' . $datePath;
        if (!is_dir($directoryPath)) {
            mkdir($directoryPath, 0755, true);
        }
        $newFilePath = $directoryPath . '/' . Yii::$app->security->generateRandomString(8) . '.docx';

        $tempFile = new TempFile();
        $tempFile->test_id = Test::findOne(['path' => $filePath])->id;
        $tempFile->path = $newFilePath;
        $tempFile->save(false);

        $newZip = new ZipArchive;
        if ($newZip->open($newFilePath, ZipArchive::CREATE) === TRUE) {
            $newZip->addFromString('word/document.xml', $modifiedXmlContent);

            // Add other necessary files from the original .docx
            $zip = new ZipArchive;
            if ($zip->open($filePath) === TRUE) {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $entry = $zip->getNameIndex($i);
                    if ($entry !== 'word/document.xml') {
                        $newZip->addFromString($entry, $zip->getFromIndex($i));
                    }
                }
                $zip->close();
            }

            $newZip->close();
        }

        return $newFilePath;
    }

    public function processAndStoreQuestions($linesArray, $test_id)
    {
        $currentQuestion = null;
        $firstAnswerProcessed = false;

        foreach ($linesArray as $lineData) {
            $lineText = $lineData['text'];

            if (preg_match('/^\s*\d+\s*\.?\s*(.+)$/u', $lineText, $matches)) {
                // Create a new question
                $currentQuestion = new Question();
                $currentQuestion->test_id = $test_id;
                $currentQuestion->content = $matches[1];
                $currentQuestion->answer_id = ''; // Set this later if needed

                $currentQuestion->save();
                $firstAnswerProcessed = false;

            } elseif (preg_match('/^\s*[a-zA-Zа-яА-ЯёЁ]\s*[.)]?\s*(.+)$/u', $lineText, $matches)) {
                if ($currentQuestion !== null) {
                    $answerText = $matches[1];
                    $answer = new Answer();
                    $answer->question_id = $currentQuestion->id;
                    $answer->content = $answerText;
                    $answer->save();

                    if (!$firstAnswerProcessed) {
                        $currentQuestion->answer_id = $answer->id;
                        $firstAnswerProcessed = true;
                        $currentQuestion->save(false);
                    }
                }
            }
        }
    }

    public function actionUpdate($id)
    {
        $test = Test::findOne($id);

        if ($this->request->isPost && $test->load($this->request->post()) && $test->save(false)) {
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'test' => $test,
        ]);
    }

    public function actionDelete($id)
    {
        if(Yii::$app->user->isGuest || !Admin::findOne(['user_id' => Yii::$app->user->identity->id])){
            return $this->redirect(['/site/login']);
        }

        $test = Test::findOne($id);
        $files = File::find()->andWhere(['test_id' => $id])->all();
        $questions = Question::find()->where(['test_id' => $id])->all();
        $teacherResults = TeacherResult::find()->andWhere(['test_id' => $id])->all();
        $testResult = TestResult::findOne(['test_id' => $id]);
        $tempFile = TempFile::findOne(['test_id' => $id]);
        $teachers = Teacher::find()->andWhere(['test_id' => $test->id])->all();

        foreach ($files as $file) {
            if(file_exists($file->path)){
                unlink($file->path);
            }
            $file->delete();
        }

        foreach ($questions as $question) {
            $answers = Answer::find()->andWhere(['question_id' => $question->id])->all();
            foreach ($answers as $a) {
                if($a->formula_path){
                    if(file_exists($a->formula_path)){
                        unlink($a->formula_path);
                    }
                }
                $a->delete();
            }
            $teacherAnswers = TeacherAnswer::find()->andWhere(['question_id' => $question->id])->all();
            foreach ($teacherAnswers as $tA) {
                $tA->delete();
            }
            if($question->formula_path){
                if(file_exists($question->formula_path)){
                    unlink($question->formula_path);
                }
            }
            $question->delete();
        }

        foreach ($teacherResults as $teacherResult) {
            $teacherResult->delete();
        }

        if ($testResult !== null && file_exists($testResult->path)) {
            unlink($testResult->path);
        }
        if ($testResult !== null) {
            $testResult->delete();
        }

        if ($tempFile !== null && file_exists($tempFile->path)) {
            unlink($tempFile->path);
        }
        if ($tempFile !== null) {
            $tempFile->delete();
        }

        foreach ($teachers as $teacher) {
            $teacher->start_time = null;
            $teacher->end_time = null;
            $teacher->save();
        }

        if(file_exists($test->path)){
            unlink($test->path);
        }
        $test->delete();

        return $this->redirect(['index']);
    }

    public function actionSettings()
    {
        return $this->render('settings');
    }

    public function actionReady($id)
    {
        $test = Test::findOne($id);
        $test->status = 'ready';
        $test->save(false);

        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionPublish($id)
    {
        $test = Test::findOne($id);
        $test->status = 'public';
        $test->save(false);

        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionEnd($id)
    {
        if(Yii::$app->user->isGuest || !Admin::findOne(['user_id' => Yii::$app->user->identity->id])){
            return $this->redirect(['/site/login']);
        }

        $test = Test::findOne($id);

        //check names in receipts
        $pdfPaths = File::find()->andWhere(['test_id' => $id])->andWhere(['type' => 'receipt'])->all();
        $fullNames = [];
        foreach ($pdfPaths as $payment) {
            $pdfPath = Yii::getAlias('@webroot/' . $payment->path);
            if (!file_exists($pdfPath)) {
                throw new \yii\web\NotFoundHttpException('The file does not exist.');
            }
            $parser = new Parser();
            $pdf = $parser->parseFile($pdfPath);
            $text = $pdf->getText();
            $normalizedText = preg_replace('/\s+/', ' ', $text);
            $normalizedText = str_replace(['—', '"'], ['-', ''], $normalizedText);
            $fullNameSection = '';
            if (preg_match('/образование(.*?)Платеж/su', $text, $matches)) {
                $fullNameSection = trim($matches[1]);
            } else {
                echo 'No text found between "образование" and "платеж" in ' . $pdfPath . '<br>';
            }
            $searchString = 'Актюбинский областной научно-практический центр - ул. Тынышбаева 43 А';
            $flag = (strpos($normalizedText, $searchString) !== false) ? '+' : '-';
            $fullNames[] = [
                'fullName' => $fullNameSection ?: 'N/A',
                'flag' => $flag
            ];
        }

        //save results in xlsx
        $results = TeacherResult::find()
            ->select(['teacher_id', 'result'])
            ->andWhere(['test_id' => $id])
            ->groupBy(['teacher_id', 'result'])
            ->orderBy(['result' => SORT_DESC])
            ->all();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Есімі');
        $sheet->setCellValue('B1', 'Мекеме');
        $sheet->setCellValue('C1', 'Нәтиже');
        $sheet->setCellValue('E1', 'Төленді');

        $row = 2;
        foreach ($results as $result) {
            $sheet->setCellValue('A' . $row, $result->teacher->name);
            $sheet->setCellValue('B' . $row, $result->teacher->school);
            $sheet->setCellValue('C' . $row, $result->result);
            $foundFlag = '-';

            foreach ($fullNames as $fullNameData) {
                if ($fullNameData['fullName'] === $result->teacher->name) {
                    $foundFlag = $fullNameData['flag'];
                    break;
                }
            }

            $sheet->setCellValue('E' . $row, $foundFlag);
            $row++;
        }
        $fileName = $test->title . '.xlsx';
        $filePath = Yii::getAlias('@webroot/results/') . $fileName;
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
        $result_pdf = new TestResult();
        $result_pdf->test_id = $id;
        $result_pdf->path = $filePath;
        $result_pdf->save(false);

        // Update test status
        $test = Test::findOne($id);
        $test->status = 'finished';
        $test->save(false);

        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionParticipants($id){

        $searchModel = new TeacherSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        $test = Test::findOne($id);

        return $this->render('participants', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'test' => $test
        ]);
    }

}
