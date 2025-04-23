<template>
    <div>
        <div class="maincontainer">
            <div class="container">
                <section class="category">
                    <h2>Категории курсов</h2>
                    <div class="category__inner">
                        <div class="category__one category__one_back1">
                            <div class="category__one-inner">
                                <div class="category__one-title">
                                    Серия курсов по C++
                                </div>
                                <div class="category__one-info">
                                    <p>• Базовый</p>
                                    <p>• Фундаментальный</p>
                                    <p>• Олимпиадный</p>
                                </div>
                                <a href="#" class="button button_white button_cat-link">Перейти в категорию</a>
                            </div>
                        </div>
                        <div class="category__one category__one_defualt">
                            <div class="category__one-inner">
                                В ближайшее время здесь <br>появятся другие категории
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <div class="b-popup" id="popup">
    		<div class="closer-big"></div>
            <div class="b-popup-content">
                <div class="popup-title"></div>
                <div class="popup-desc"></div>
                <div class="form-block">
                    <form method="post" name="mtForm1" id="mtForm1" class="forma">
                        <input type="hidden" name="data_form" id="data_form" value="">
                        <input type="hidden" name="no" value="no" />
                        <div class="form-field">
                            <label>
                                <span>Ваше имя:</span>
                                <input type="text" name="name" required="required">
                            </label>
                        </div>
                        <div class="form-field">
                            <label>
                                <span>Ваш телефон:</span>
                                <input type="tel" name="phone" required="required" class="phone_valid">
                            </label>
                        </div>
                        <div class="form-field">
                            <input type="submit" name="submit" value="Отправить заявку">
                        </div>
                    </form>
                </div>
                <div class="close-up">
                    <div></div>
                    <div></div>
                </div>
            </div>
		</div>
        <div class="b-popup" id="message">
            <div class="closer-big closer-big-all"></div>
            <div class="b-popup-content">
                <div class="popup-title">Cпасибо вам!</div>
                <div class="popup-desc">Ваша заявка успешно отправлена. <br>C вами свяжутся в ближайшее время.</div>
                <div class="close-up close-up-all">
                    <div></div>
                    <div></div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
</script>