<?php

namespace frontend\controllers;

use common\models\Admin;
use common\models\Answer;
use common\models\Certificate;
use common\models\File;
use common\models\Percentage;
use common\models\Purpose;
use common\models\Question;
use common\models\Teacher;
use common\models\TeacherAnswer;
use common\models\TeacherSearch;
use common\models\Test;
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
use yii\web\HttpException;
use yii\web\Response;
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

    public function actionCreate()
    {
        $model = new Test();

        if ($this->request->isPost) {

            $model->file = UploadedFile::getInstance($model, 'file');

            if ($model->load($this->request->post())) {

                if ($model->file) {
                    $directoryPath = 'tests/' . $model->subject->title;
                    if (!is_dir($directoryPath)) {
                        mkdir($directoryPath, 0755, true);
                    }
                    $filePath = $directoryPath . '/' . $model->language . '_' . $model->version . '_'
                        . date('H_i_s') . '.' . $model->file->extension;

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

        $test = Test::findOne(['path' => $filePath]);
        $directoryPath = 'tests/' . $test->subject->title;
        if (!is_dir($directoryPath)) {
            mkdir($directoryPath, 0755, true);
        }
        $newFilePath = $directoryPath . '/' . Yii::$app->security->generateRandomString(8) . '.docx';

        $tempFile = new File();
        $tempFile->test_id = Test::findOne(['path' => $filePath])->id;
        $tempFile->teacher_id = null;
        $tempFile->type = 'temporary';
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

        return $this->redirect(['participants', 'id' => $id]);
    }

    public function actionEnd($id)
    {
        if(Yii::$app->user->isGuest || !Admin::findOne(['user_id' => Yii::$app->user->identity->id])){
            return $this->redirect(['/site/login']);
        }

        $test = Test::findOne($id);

        $test = Test::findOne($id);
        $test->status = 'finished';
        $test->save(false);

        return $this->redirect(['participants', 'id' => $id]);
    }

    public function actionResult($id){
        //check names in receipts
        $files = File::find()->andWhere(['test_id' => $id])->andWhere(['type' => 'receipt'])->all();
        $fullNames = [];
        foreach ($files as $file) {
            $path = Yii::getAlias('@webroot/' . $file->path);
            if (!file_exists($path)) {
                throw new \yii\web\NotFoundHttpException('The file does not exist.');
            }
            $parser = new Parser();
            $pdf = $parser->parseFile($path);
            $text = $pdf->getText();
            $normalizedText = preg_replace('/\s+/', ' ', $text);
            $normalizedText = str_replace(['—', '"'], ['-', ''], $normalizedText);
            $fullNameSection = '';
            if (preg_match('/образование(.*?)Платеж/su', $normalizedText, $matches)) {
                $fullNameSection = trim($matches[1]);
            } else {
                echo 'No text found between "образование" and "платеж" in ' . $path . '<br>';
            }
            $searchString = 'Актюбинский областной научно-практический центр - ул. Тынышбаева 43 А';
            $flag = (strpos($normalizedText, $searchString) !== false && strpos($normalizedText, '5 000,00 ₸') !== false) ? '1' : '0';
            $fullNames[] = [
                'fullName' => $fullNameSection ?: 'N/A',
                'flag' => $flag
            ];
        }

        //save results in xlsx
        $teachers = Teacher::find()->andWhere(['test_id' => $id])->all();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Есімі');
        $sheet->setCellValue('B1', 'Мекеме');
        $sheet->setCellValue('C1', 'Нәтиже');
        $sheet->setCellValue('D1', 'Төленді');

        $row = 2;
        foreach ($teachers as $teacher) {
            $sheet->setCellValue('A' . $row, $teacher->name);
            $sheet->setCellValue('B' . $row, $teacher->school);
            $sheet->setCellValue('C' . $row, $teacher->result);

            $foundFlag = '0';
            foreach ($fullNames as $fullNameData) {
                if($fullNameData['fullName'] === $teacher->name){
                    $foundFlag = $fullNameData['flag'];
                    break;
                }
            }

            $sheet->setCellValue('D' . $row, $foundFlag);
            $row++;
        }

        $test = Test::findOne($id);
        $directoryPath = 'results/' . $test->subject->title;
        if (!is_dir($directoryPath)) {
            mkdir($directoryPath, 0755, true);
        }
        $filePath = $directoryPath . '/' . $test->subject->title . '_' . $test->language . '_' . $test->version . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        $file = new File();
        $file->test_id = $id;
        $file->teacher_id = null;
        $file->type = 'result';
        $file->path = $filePath;
        $file->save(false);

        return Yii::$app->response->sendFile($file->path);
    }

    public function actionPresent($id)
    {
        if(Yii::$app->user->isGuest || !Admin::findOne(['user_id' => Yii::$app->user->identity->id])){
            return $this->redirect(['/site/login']);
        }

        $test = Test::findOne($id);
        $test->status = 'certificated';
        $test->save(false);

        //send certificates
        $topResults = Teacher::find()
            ->andWhere(['test_id' => $id])
            ->orderBy(['result' => SORT_DESC])
            ->all();
        $firstPlace = [];
        $secondPlace = [];
        $thirdPlace = [];
        $goodResults = [];
        $certificateResults = [];
        $percentage = Percentage::find()->one();
        foreach ($topResults as $result) {
            if ($result->result >= $percentage->first) {
                $firstPlace[] = $result;
            }
            else if ($result->result >= $percentage->second) {
                $secondPlace[] = $result;
            }
            else if ($result->result >= $percentage->third) {
                $thirdPlace[] = $result;
            }
            else if ($result->result >= $percentage->fourth) {
                $goodResults[] = $result;
            }
            else if ($result->result >= $percentage->fifth) {
                $certificateResults[] = $result;
            }
        }
        foreach ($firstPlace as $result) {
            $this->certificate(Teacher::findOne($result->id), Test::findOne($id), 1);
        }
        foreach ($secondPlace as $result) {
            $this->certificate(Teacher::findOne($result->id), Test::findOne($id), 2);
        }
        foreach ($thirdPlace as $result) {
            $this->certificate(Teacher::findOne($result->id), Test::findOne($id), 3);
        }
        foreach ($goodResults as $result) {
            $this->certificate(Teacher::findOne($result->id), Test::findOne($id), 4);
        }
        foreach ($certificateResults as $result) {
            $this->certificate(Teacher::findOne($result->id), Test::findOne($id), 5);
        }

        return $this->redirect(['participants', 'id' => $id]);
    }

    public function certificate($teacher, $test, $place)
    {
        $cert = Certificate::findOne(['subject_id' => $test->subject_id])->file_name;
        $imgPath = Yii::getAlias("@webroot/certificate_templates/{$place}/{$cert}");
        $image = imagecreatefromjpeg($imgPath);
        $textColor = imagecolorallocate($image, 227, 41, 29);
        $fontPath = Yii::getAlias('@frontend/fonts/times.ttf');

        //writing name
        $averageCharWidth = 9.5;
        $numChars = strlen($teacher->name);
        $textWidth = $numChars * $averageCharWidth;
        $cx = 950;
        $x = (int)($cx - ($textWidth / 2));
        imagettftext($image, 28, 0, $x, 760, $textColor, $fontPath, $teacher->name);

        //writing number
        $formattedId = str_pad($teacher->id, 5, '0', STR_PAD_LEFT);
        imagettftext($image, 28, 0, 1480, 1100, $textColor, $fontPath, $formattedId);

        $directoryPath = 'certificates/' . $teacher->subject->title;
        if (!is_dir($directoryPath)) {
            mkdir($directoryPath, 0755, true);
        }
        $subjectTitle = str_replace(' ', '_', $test->subject->title);
        $teacherName = str_replace(' ', '_', $teacher->name);
        $newPath = $directoryPath . '/'
            . $subjectTitle . '_'
            . $teacher->test->language . '_'
            . $teacher->test->version . '_'
            . $teacherName . '.'
            . '.jpeg';
        imagejpeg($image, $newPath);
        imagedestroy($image);

        $certificate = new File();
        $certificate->teacher_id = $teacher->id;
        $certificate->test_id = $test->id;
        $certificate->type = 'certificate';
        $certificate->path = $newPath;
        $certificate->save(false);
    }

    public function actionJournal($id)
    {
        if(Yii::$app->user->isGuest || !Admin::findOne(['user_id' => Yii::$app->user->identity->id])){
            return $this->redirect(['/site/login']);
        }

        //save results in xlsx
        $teachers = Teacher::find()->andWhere(['test_id' => $id])->all();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Есімі');
        $sheet->setCellValue('B1', 'Мекеме');
        $sheet->setCellValue('C1', 'Нәтиже');
        $sheet->setCellValue('D1', 'Төленді');

        $row = 2;
        foreach ($teachers as $teacher) {
            $sheet->setCellValue('A' . $row, $teacher->name);
            $sheet->setCellValue('B' . $row, $teacher->school);
            $sheet->setCellValue('C' . $row, $teacher->result);

            $foundFlag = '0';
            foreach ($fullNames as $fullNameData) {
                if($fullNameData['fullName'] === $teacher->name){
                    $foundFlag = $fullNameData['flag'];
                    break;
                }
            }

            $sheet->setCellValue('D' . $row, $foundFlag);
            $row++;
        }

        $test = Test::findOne($id);
        $directoryPath = 'results/' . $test->subject->title;
        if (!is_dir($directoryPath)) {
            mkdir($directoryPath, 0755, true);
        }
        $filePath = $directoryPath . '/' . $test->subject->title . $test->language . '_'
            . $test->version . '_' . date('H_i_s') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        $file = new File();
        $file->test_id = $id;
        $file->teacher_id = null;
        $file->type = 'result';
        $file->path = $filePath;
        $file->save(false);

        return Yii::$app->response->sendFile($file->path);
    }

    public function actionDownloadZip($id){
        if(Yii::$app->user->isGuest || !Admin::findOne(['user_id' => Yii::$app->user->identity->id])){
            return $this->redirect(['/site/login']);
        }

        $filePaths = File::find()
            ->andWhere(['test_id' => $id])
            ->andWhere(['type' => 'certificate'])
            ->all();
        $test = Test::findOne($id);
        $subjectTitle = str_replace(' ', '_', $test->subject->title);
        $zipFileName = $subjectTitle . '_'
            . $test->language . '_'
            . $test->version . '_'
            . 'сертификаттар.zip';

        $zip = new \ZipArchive();
        $zipFilePath = Yii::getAlias('@webroot/uploads/' . $zipFileName);
        if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
            throw new HttpException(500, 'Could not create ZIP file.');
        }
        foreach ($filePaths as $filePath) {
            if (file_exists($filePath->path)) {
                $zip->addFile($filePath->path, basename($filePath->path));
            } else {
                Yii::error("File not found: $filePath->path");
            }
        }
        $zip->close();

        return Yii::$app->response->sendFile($zipFilePath)->on(Response::EVENT_AFTER_SEND, function () use ($zipFilePath) {
            @unlink($zipFilePath);
        });
    }

    public function actionSettings()
    {
        $percentage = Percentage::find()->one();

        if ($percentage->load(Yii::$app->request->post()) && $percentage->save()) {
            return $this->redirect(['settings']);
        }

        $purpose = Purpose::find()->one() ?: new Purpose();

        if ($purpose->load(Yii::$app->request->post()) && $purpose->save()) {
            return $this->redirect(['settings']);
        }

        return $this->render('settings', [
            'percentage' => $percentage,
            'purpose' => $purpose,
        ]);
    }

}
