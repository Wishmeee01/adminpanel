<?php

namespace backend\controllers;

use backend\models\Gallery;
use backend\models\GalleryySearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * GalleryController implements the CRUD actions for Gallery model.
 */
class GalleryController extends Controller {

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
     * Lists all Gallery models.
     *
     * @return string
     */
    public function actionIndex() {
        $searchModel = new GalleryySearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Gallery model.
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
     * Creates a new Gallery model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate() {
        $model = new Gallery();

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {

                $path = \Yii::getAlias('@webroot') . '/uploads/';
                $upload_file = '';
                $document_upload = '';
                $link = '';
                $s3 = \Yii::$app->get('s3');
                $image = UploadedFile::getInstance($model, 'image_link');
                if (!is_null($image)) {
                    $ext = pathinfo($image->name, PATHINFO_EXTENSION);
                    $type = $image->type;
                    $types = explode('/', $type);
                    $folder = 'images/';

                    $document_upload = time() . ".{$ext}";
                    $upload_file = 'uploads/' . $document_upload;
                    $path = $path . $document_upload;
                    $image->saveAs($path);
                    $result = $s3->upload($folder . $document_upload, $path);
                    $link = $result['ObjectURL'];
                }

                $model->category_id = $_POST['Gallery']['category_id'];
                $model->image_link = $link;
                $model->uploaded_at = time();
                $model->status = 1;
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
     * Updates an existing Gallery model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id) {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post())) {
            $imgl = $this->findModel($id);
            $path = \Yii::getAlias('@webroot') . '/uploads/';
            $upload_file = '';
            $document_upload = '';
            $link = '';
            $s3 = \Yii::$app->get('s3');
            $image = UploadedFile::getInstance($model, 'image_link');
            if (!is_null($image)) {
                $ext = pathinfo($image->name, PATHINFO_EXTENSION);
                $type = $image->type;
                $types = explode('/', $type);
                $folder = 'images/';

                $document_upload = time() . ".{$ext}";
                $upload_file = 'uploads/' . $document_upload;
                $path = $path . $document_upload;
                $image->saveAs($path);
                $result = $s3->upload($folder . $document_upload, $path);
                $link = $result['ObjectURL'];
            } else {
                $link = $imgl->image_link;
            }
            
            $model->category_id = $_POST['Gallery']['category_id'];
            $model->image_link = $link;
            $model->uploaded_at = time();
            $model->status = 1;
            $model->save(false);

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
                    'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Gallery model.
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
     * Finds the Gallery model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Gallery the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = Gallery::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

}
