<?php

namespace backend\controllers;

use backend\models\Subscription;
use backend\models\Offers;
use backend\models\SubscriptionSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * SubscriptionController implements the CRUD actions for Subscription model.
 */
class SubscriptionController extends Controller {

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
     * Lists all Subscription models.
     *
     * @return string
     */
    public function actionIndex() {
        $searchModel = new SubscriptionSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Subscription model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id) {
        $model =  $this->findModel($id);
        $offers = Offers::find()->where(['subscription_id' => $model->id])->one();
        if (empty($offers)) {
            $offers = new Offers();
        }
        return $this->render('view', [
                    'model' => $model,
                    'offers' => $offers
        ]);
    }

    /**
     * Creates a new Subscription model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate() {
        $model = new Subscription();
        $offers = new Offers();

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {

                $path = \Yii::getAlias('@webroot') . '/uploads/';
                $upload_file = '';
                $document_upload = '';
                $link = '';
                $s3 = \Yii::$app->get('s3');
                $image = UploadedFile::getInstance($model, 'icon');
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

                $model->plan_name = $_POST['Subscription']['plan_name'];
                $model->validity_in_days = $_POST['Subscription']['validity_in_days'];
                $model->cycle = $_POST['Subscription']['cycle'];
                $model->currency = $_POST['Subscription']['currency'];
                $model->amount = $_POST['Subscription']['amount'];
                $model->description = $_POST['Subscription']['description'];
                $model->offer_status = $_POST['Subscription']['offer_status'];
                $model->icon = $link;
                $model->status = 1;
                $model->save(false);

                if ($_POST['Subscription']['offer_status'] == 1) {
                    $offers->subscription_id = $model->id;
                    $offers->offer_name = $_POST['Offers']['offer_name'];
                    $offers->offer_price = $_POST['Offers']['offer_price'];
                    $offers->offer_start_date = date('Y-m-d', strtotime($_POST['Offers']['offer_start_date']));
                    $offers->offer_end_date = date('Y-m-d', strtotime($_POST['Offers']['offer_end_date']));
                    $offers->createdAt = date('Y-m-d');
                    $offers->updatedAt = date('Y-m-d');
                    $offers->status = 1;
                    $offers->save(false);
                }

                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
                    'model' => $model,
                    'offers' => $offers,
        ]);
    }

    /**
     * Updates an existing Subscription model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id) {
        $model = $this->findModel($id);
        $offers = Offers::find()->where(['subscription_id' => $model->id])->one();
        if (empty($offers)) {
            $offers = new Offers();
        }

        if ($this->request->isPost && $model->load($this->request->post())) {

            $imgl = $this->findModel($id);
            $path = \Yii::getAlias('@webroot') . '/uploads/';
            $upload_file = '';
            $document_upload = '';
            $link = '';
            $s3 = \Yii::$app->get('s3');
            $image = UploadedFile::getInstance($model, 'icon');
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
                $link = $imgl->icon;
            }

            $model->plan_name = $_POST['Subscription']['plan_name'];
            $model->validity_in_days = $_POST['Subscription']['validity_in_days'];
            $model->cycle = $_POST['Subscription']['cycle'];
            $model->currency = $_POST['Subscription']['currency'];
            $model->amount = $_POST['Subscription']['amount'];
            $model->description = $_POST['Subscription']['description'];
            $model->offer_status = $_POST['Subscription']['offer_status'];
            $model->icon = $link;
            $model->status = 1;
            $model->save(false);

            if ($_POST['Subscription']['offer_status'] == 1) {
                $offers->subscription_id = $model->id;
                $offers->offer_name = $_POST['Offers']['offer_name'];
                $offers->offer_price = $_POST['Offers']['offer_price'];
                $offers->offer_start_date = date('Y-m-d', strtotime($_POST['Offers']['offer_start_date']));
                $offers->offer_end_date = date('Y-m-d', strtotime($_POST['Offers']['offer_end_date']));
                $offers->updatedAt = date('Y-m-d h:i:s');
                $offers->status = 1;
                $offers->save(false);
            }

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
                    'model' => $model,
                    'offers' => $offers,
        ]);
    }

    /**
     * Deletes an existing Subscription model.
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
     * Finds the Subscription model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Subscription the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = Subscription::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

}
