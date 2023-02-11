<?php

namespace backend\controllers;

use backend\models\User;
use backend\models\UserSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller {

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
     * Lists all User models.
     *
     * @return string
     */
    public function actionIndex() {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id) {
        $details = \backend\models\UserDetails::findOne(['user_id' => $id]);
        return $this->render('view', [
                    'model' => $this->findModel($id),
                    'details' => $details
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate() {
        $model = new User();
        $details = new \backend\models\UserDetails();

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {

                $path = \Yii::getAlias('@webroot') . '/uploads/';
                $upload_file = '';
                $document_upload = '';
                $link = '';
                $s3 = \Yii::$app->get('s3');
                $image = UploadedFile::getInstance($details, 'profile_image');
                if (!is_null($image)) {
                    $ext = pathinfo($image->name, PATHINFO_EXTENSION);
                    $document_upload = time() . ".{$ext}";
                    $upload_file = 'uploads/' . $document_upload;
                    $path = $path . $document_upload;
                    $image->saveAs($path);
                    $result = $s3->upload('images/' . $document_upload, $path);
                    $link = $result['ObjectURL'];
                }

                $model->username = $_POST['User']['mobile'];
                $model->user_token = $this->generateApiKey();
                $model->auth_key = \Yii::$app->security->generateRandomString();
                $model->password_hash = \Yii::$app->security->generatePasswordHash($_POST['User']['mobile']);
                $model->email = $_POST['User']['email'];
                $model->mobile = $_POST['User']['mobile'];
                $model->status = 10;
                $model->created_at = time();
                $model->updated_at = time();
                $model->save(false);

                $details->user_id = $model->id;
                $details->first_name = $_POST['UserDetails']['first_name'];
                $details->last_name = $_POST['UserDetails']['last_name'];
                $details->date_of_birth = $_POST['UserDetails']['date_of_birth'];
                $details->anniversary_date = $_POST['UserDetails']['anniversary_date'];
                $details->country = $_POST['UserDetails']['country'];
                $details->profile_image = $link;
                $details->created = time();
                $details->updated = time();
                $details->save(false);

                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
                    'model' => $model,
                    'details' => $details,
        ]);
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id) {
        $model = $this->findModel($id);
        $details = \backend\models\UserDetails::findOne(['user_id' => $id]);

        if ($this->request->isPost && $model->load($this->request->post())) {

            $path = \Yii::getAlias('@webroot') . '/uploads/';
            $upload_file = '';
            $document_upload = '';
            $s3 = \Yii::$app->get('s3');
            $image = UploadedFile::getInstance($details, 'profile_image');
            if (!is_null($image)) {
                $ext = pathinfo($image->name, PATHINFO_EXTENSION);
                $document_upload = time() . ".{$ext}";
                $upload_file = 'uploads/' . $document_upload;
                $path = $path . $document_upload;
                $image->saveAs($path);
                $result = $s3->upload('images/' . $document_upload, $path);
                $link = $result['ObjectURL'];
            } else {
                $link = $details->profile_image;
            }

            $model->username = $_POST['User']['mobile'];
            $model->email = $_POST['User']['email'];
            $model->mobile = $_POST['User']['mobile'];
            $model->save(false);

            $details->first_name = $_POST['UserDetails']['first_name'];
            $details->last_name = $_POST['UserDetails']['last_name'];
            $details->date_of_birth = $_POST['UserDetails']['date_of_birth'];
            $details->anniversary_date = $_POST['UserDetails']['anniversary_date'];
            $details->country = $_POST['UserDetails']['country'];
            $details->profile_image = $link;
            $details->updated = time();
            $details->save(false);

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
                    'model' => $model,
                    'details' => $details,
        ]);
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id) {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    protected function generateApiKey() {
        return md5(uniqid(rand(), true));
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = User::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

}
