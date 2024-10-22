<?php

namespace frontend\controllers;

use common\models\Teacher;
use common\models\TeacherSearch;
use common\models\User;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class TeacherController extends Controller
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
        $searchModel = new TeacherSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionCreate()
    {
        $user = new User();
        $teacher = new Teacher();

        if ($user->load(Yii::$app->request->post()) && $teacher->load(Yii::$app->request->post())) {

            $user->generateAuthKey();
            $user->setPassword($user->password);
            $user->save();

            $teacher->user_id = $user->id;
            $teacher->save();

            return $this->redirect('/teacher/index');
        }

        return $this->render('create', [
            'user' => $user,
            'teacher' => $teacher,
        ]);
    }

    public function actionUpdate($id)
    {
        $teacher = $this->findModel($id);
        $user = User::findOne($teacher->user_id);

        if ($user->load(Yii::$app->request->post()) && $teacher->load(Yii::$app->request->post())) {

            if(!empty($teacher->password)){
                $user->setPassword($teacher->password);
                $user->save(false);
            }

            $teacher->save();

            return $this->redirect('/teacher/index');
        }

        return $this->render('update', [
            'user' => $user,
            'teacher' => $teacher,
        ]);
    }

    public function actionDelete($id)
    {
        $teacher = Teacher::findOne($id);
        $user = User::findOne($teacher->user_id);
        $teacher->delete();
        $user->delete();

        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = Teacher::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
