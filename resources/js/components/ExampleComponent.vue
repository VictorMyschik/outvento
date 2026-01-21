<template>
  <section class="breadcumb-section">
    <div class="tf-container">
      <div class="row">
        <div class="col-lg-12 center z-index1">
          <h1 class="title">{{ t('login') }}</h1>
          <img class="bcrumb-ab" src="@/assets/images/page/login/mask-bcrumb.png" alt="">
        </div>
      </div>

    </div>
  </section>

  <section class="login">
    <div class="tf-container">
      <div class="row">
        <div class="col-lg-12">
          <div class="login-wrap flex">
            <div class="image">
              <img :src="randomImage" alt="image">
            </div>
            <div class="content">
              <h3>{{ t('login_title') }}</h3>
              <h6 v-if="error" class="text-danger">{{ error }}</h6>
              <form @submit.prevent="submit" class="login-user">
                <div class="row">
                  <div class="col-lg-12">
                    <div class="input-wrap">
                      <label>{{ t('login') }}</label>
                      <input type="text" required v-model="login" id="login"
                             :placeholder="t('login_placeholder')">
                    </div>

                  </div>
                  <div class="col-lg-12">
                    <div class="input-wrap">
                      <div class="flex-two">
                        <label> {{ t('password') }}</label>
                        <RouterLink to="/forgot-password" class="mb-15">
                          {{ t('forgot_password') }}
                        </RouterLink>
                      </div>
                      <div class="input-group">
                        <input
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            class="form-control"
                            v-model="password"
                            required
                            @blur="hide"
                            :placeholder="t('password_placeholder')"
                        />

                        <span
                            class="input-group-text"
                            style="cursor: pointer"
                            @mousedown.prevent="show"
                            @mouseup="hide"
                            @mouseleave="hide"
                            @touchstart.prevent="toggle"
                        >
                          <i class="fa" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                          </span>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-12 mb-3">
                    <input class="form-check-input" type="checkbox" id="remember"
                           v-model="remember"/>
                    <label class="form-check-label" for="remember">
                      {{ t('remember_me') }}
                    </label>
                  </div>
                  <div class="col-lg-12 mb-40">
                    <div class="input-wrap-social ">
                      <span class="or">{{ t('or') }}</span>
                      <div class="flex-three">
                        <a href="#" class="login-social flex-three">
                          <img src="@/assets/images/page/login/gg.png" alt="image">
                          <span>{{ t('sign_in_google') }}</span>
                        </a>
                        <a href="#" class="login-social flex-three">
                          <img src="@/assets/images/page/login/fb.png" alt="image">
                          <span>{{ t('sign_in_facebook') }}</span>
                        </a>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-12 mb-30">
                    <button type="submit" class="btn-submit">{{ t('login_let_me_in') }}</button>
                  </div>
                  <div class="col-md-12">
                    <div class="flex-three">
                      <span class="account">{{ t('no_account') }}</span>
                      <RouterLink to="/register" class="link-login">
                        {{ t('register') }}
                      </RouterLink>
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
export default {
  mounted() {
    console.log('Component mounted.')
  }
}
</script>
