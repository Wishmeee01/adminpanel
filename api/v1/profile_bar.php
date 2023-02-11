 <div class="col-lg-3 col-md-3 col-sm-4 col-xs-12 tab-custom-width">
       <div class="left-box-tabmake">
                <ul class="tab_links_ul_holder nav nav-tabs">
                    <li class="<?php if($activeIndex == 1){ echo 'active'; }?>"><a href="<?= yii::$app->urlManager->createUrl(['site/myaccount']); ?>">Address</a></li>
                
                    <li class="<?php if($activeIndex == 2){ echo 'active'; }?>"><a href="<?= yii::$app->urlManager->createUrl(['site/changepassword']); ?>">Change Password</a></li>
                </ul>
        </div>
 </div> 
 