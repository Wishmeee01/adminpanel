<?php

namespace backend\controllers;

use backend\models\Media;
use backend\models\MediaSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * MediaController implements the CRUD actions for Media model.
 */
class MediaController extends Controller {

    /**
     * @inheritDoc
     */
    public function behaviors() {
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

    /**
     * Lists all Media models.
     *
     * @return string
     */
    public function actionIndex() {
        $searchModel = new MediaSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Media model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id) {
        return $this->render('view', [
                    'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Media model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate() {
        $model = new Media();

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {

                $path = \Yii::getAlias('@webroot') . '/uploads/';
                $upload_file = '';
                $document_upload = '';
                $link = '';
                $s3 = \Yii::$app->get('s3');
                $image = UploadedFile::getInstance($model, 'link');
                if (!is_null($image)) {
                    $ext = pathinfo($image->name, PATHINFO_EXTENSION);
                    $type = $image->type;
                    $types = explode('/', $type);

                    if ($types[0] == 'image') {
                        $folder = 'images/';
                        $key_type = 'images';
                    } else if ($types[0] == 'video') {
                        $folder = 'videos/';
                        $key_type = 'videos';
                    } else if ($types[0] == 'audio') {
                        $folder = 'audios/';
                        $key_type = 'audios';
                    } else {
                        $folder = 'others/';
                        $key_type = 'others';
                    }
                    $document_upload = time() . ".{$ext}";
                    $upload_file = 'uploads/' . $document_upload;
                    $path = $path . $document_upload;
                    $image->saveAs($path);
                    $result = $s3->upload($folder . $document_upload, $path);
                    $link = $result['ObjectURL'];
                }



                $model->user_id = $_POST['Media']['user_id'];
                $model->friend_id = $_POST['Media']['friend_id'];
                $model->title = $_POST['Media']['title'];
                $model->link = $link;
                $model->tags = $_POST['Media']['tags'];
                $model->description = $_POST['Media']['description'];
                $model->type = $key_type;
                $model->month = date('m');
                $model->year = date('Y');
                $model->status = 1;
                $model->created = time();
                $model->save(false);

                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
                    'model' => $model,
        ]);
    }

    /**
     * Updates an existing Media model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id) {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post())) {

            $path = \Yii::getAlias('@webroot') . '/uploads/';
            $upload_file = '';
            $document_upload = '';
            $link = '';
            $s3 = \Yii::$app->get('s3');
            $image = UploadedFile::getInstance($model, 'link');

            if (empty($image)) {
                $link = $model->link;
                $key_type = $model->type;
            } else {

                $ext = pathinfo($image->name, PATHINFO_EXTENSION);
                $type = $image->type;
                $types = explode('/', $type);

                if ($types[0] == 'image') {
                    $folder = 'images/';
                    $key_type = 'images';
                } else if ($types[0] == 'video') {
                    $folder = 'videos/';
                    $key_type = 'videos';
                } else if ($types[0] == 'audio') {
                    $folder = 'audios/';
                    $key_type = 'audios';
                } else {
                    $folder = 'others/';
                    $key_type = 'others';
                }
                $document_upload = time() . ".{$ext}";
                $upload_file = 'uploads/' . $document_upload;
                $path = $path . $document_upload;
                $image->saveAs($path);
                $result = $s3->upload($folder . $document_upload, $path);
                $link = $result['ObjectURL'];
            }


            $model->user_id = $_POST['Media']['user_id'];
            $model->friend_id = $_POST['Media']['friend_id'];
            $model->title = $_POST['Media']['title'];
            $model->link = $link;
            $model->tags = $_POST['Media']['tags'];
            $model->description = $_POST['Media']['description'];
            $model->type = $key_type;
            $model->month = date('m');
            $model->year = date('Y');
            $model->save(false);

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
                    'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Media model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id) {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Media model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Media the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = Media::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

}
