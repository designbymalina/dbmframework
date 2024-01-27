    <?php
        // Navigation
        include(BASE_DIRECTORY . 'templates/_include/navigation.html.php');
        // Flash messages
        include(BASE_DIRECTORY . 'templates/_include/messages.html.php');
        // Code
        !empty($data['data.form']) ? $form = $data['data.form'] : $form = null;
    ?>
    <!-- Main Content -->
    <section class="h-100 dbm-form-gradient-1">
        <div class="container py-5 h-100">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col-xl-10">
                    <div class="card rounded-3 text-black">
                        <div class="row g-0">
                            <div class="col-lg-6">
                                <div class="card-body p-md-5 mx-md-4">
                                    <form action="<?php echo path('register/signup'); ?>" method="POST" class="dbm-form-primary">
                                        <h4 class="mt-1 mb-4 pb-1">Utwórz nowe konto</h4>
                                        <div class="form-outline mb-2">
                                            <label class="form-label mb-1" for="form_login"><?php Dbm\Classes\TemplateClass::trans('username'); ?></label>
                                            <div class="input-group">
                                                <span class="input-group-text" id="basic_addon_3"><i class="bi bi-person"></i></span>
                                                <input type="text" name="dbm_login" value="<?php if (!empty($form['login'])): echo $form['login']; endif; ?>" id="form_login" class="form-control" placeholder="<?php Dbm\Classes\TemplateClass::trans('your_login'); ?>" minlength="2" maxlength="30" required>
                                            </div>
                                            <?php if (!empty($form['error_login'])): echo '<div class="text-danger small"><i class="bi bi-info-circle-fill me-2 small"></i>' . $form['error_login'] . '</div>' . "\n"; endif; ?>
                                        </div>
                                        <div class="form-outline mb-2">
                                            <label class="form-label mb-1" for="form_email"><?php Dbm\Classes\TemplateClass::trans('email'); ?></label>
                                            <div class="input-group">
                                                <span class="input-group-text" id="basic_addon_1">@</span>
                                                <input type="email" name="dbm_email" value="<?php if (!empty($form['email'])): echo $form['email']; endif; ?>" id="form_email" class="form-control" placeholder="<?php Dbm\Classes\TemplateClass::trans('email_address'); ?>" required>
                                            </div>
                                            <?php if (!empty($form['error_email'])): echo '<div class="text-danger small"><i class="bi bi-info-circle-fill me-2 small"></i>' . $form['error_email'] . '</div>' . "\n"; endif; ?>
                                        </div>
                                        <div class="form-outline mb-2">
                                            <label class="form-label mb-1" for="form_password"><?php Dbm\Classes\TemplateClass::trans('password'); ?></label>
                                            <div class="input-group">
                                                <span class="input-group-text" id="basic_addon_2"><i class="bi bi-lock"></i></span>
                                                <input type="password" name="dbm_password" id="form_password" class="form-control" placeholder="<?php Dbm\Classes\TemplateClass::trans('password'); ?>" required>
                                            </div>
                                            <?php if (!empty($form['error_password'])): echo '<div class="text-danger small"><i class="bi bi-info-circle-fill me-2 small"></i>' . $form['error_password'] . '</div>' . "\n"; endif; ?>
                                        </div>
                                        <div class="form-outline mb-2">
                                            <label class="form-label mb-1" for="form_confirmation">Powtórz hasło</label>
                                            <div class="input-group">
                                                <span class="input-group-text" id="basic_addon_2"><i class="bi bi-lock"></i></span>
                                                <input type="password" name="dbm_confirmation" id="form_confirmation" class="form-control" placeholder="<?php Dbm\Classes\TemplateClass::trans('password'); ?>" data-rule-equalTo="#form_password" required>
                                            </div>
                                            <?php if (!empty($form['error_confirmation'])): echo '<div class="text-danger small"><i class="bi bi-info-circle-fill me-2 small"></i>' . $form['error_confirmation'] . '</div>' . "\n"; endif; ?>
                                        </div>
                                        <div class="d-grid gap-2 mb-2 mt-4 text-center">
                                            <button type="submit" class="btn btn-primary btn-block text-uppercase fw-bold dbm-btn-primary"><?php Dbm\Classes\TemplateClass::trans('register_to'); ?></button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="col-lg-6 d-flex align-items-center position-relative dbm-background-1">
                                <div class="text-white px-3 py-4 p-md-5 mx-md-4 ">
                                    <div class="position-absolute top-0 end-0 mt-4 me-3 d-none d-xxl-block">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" fill="currentColor" class="bi bi-rocket" viewBox="0 0 16 16">
                                            <path d="M8 8c.828 0 1.5-.895 1.5-2S8.828 4 8 4s-1.5.895-1.5 2S7.172 8 8 8Z"/>
                                            <path d="M11.953 8.81c-.195-3.388-.968-5.507-1.777-6.819C9.707 1.233 9.23.751 8.857.454a3.495 3.495 0 0 0-.463-.315A2.19 2.19 0 0 0 8.25.064.546.546 0 0 0 8 0a.549.549 0 0 0-.266.073 2.312 2.312 0 0 0-.142.08 3.67 3.67 0 0 0-.459.33c-.37.308-.844.803-1.31 1.57-.805 1.322-1.577 3.433-1.774 6.756l-1.497 1.826-.004.005A2.5 2.5 0 0 0 2 12.202V15.5a.5.5 0 0 0 .9.3l1.125-1.5c.166-.222.42-.4.752-.57.214-.108.414-.192.625-.281l.198-.084c.7.428 1.55.635 2.4.635.85 0 1.7-.207 2.4-.635.067.03.132.056.196.083.213.09.413.174.627.282.332.17.586.348.752.57l1.125 1.5a.5.5 0 0 0 .9-.3v-3.298a2.5 2.5 0 0 0-.548-1.562l-1.499-1.83ZM12 10.445v.055c0 .866-.284 1.585-.75 2.14.146.064.292.13.425.199.39.197.8.46 1.1.86L13 14v-1.798a1.5 1.5 0 0 0-.327-.935L12 10.445ZM4.75 12.64C4.284 12.085 4 11.366 4 10.5v-.054l-.673.82a1.5 1.5 0 0 0-.327.936V14l.225-.3c.3-.4.71-.664 1.1-.861.133-.068.279-.135.425-.199ZM8.009 1.073c.063.04.14.094.226.163.284.226.683.621 1.09 1.28C10.137 3.836 11 6.237 11 10.5c0 .858-.374 1.48-.943 1.893C9.517 12.786 8.781 13 8 13c-.781 0-1.517-.214-2.057-.607C5.373 11.979 5 11.358 5 10.5c0-4.182.86-6.586 1.677-7.928.409-.67.81-1.082 1.096-1.32.09-.076.17-.135.236-.18Z"/>
                                            <path d="M9.479 14.361c-.48.093-.98.139-1.479.139-.5 0-.999-.046-1.479-.139L7.6 15.8a.5.5 0 0 0 .8 0l1.079-1.439Z"/>
                                        </svg>
                                    </div>
                                    <h4 class="mb-4"><?php Dbm\Classes\TemplateClass::trans('login.subheader'); ?></h4>
                                    <p class="small mb-0"><?php Dbm\Classes\TemplateClass::trans('index.lead'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
