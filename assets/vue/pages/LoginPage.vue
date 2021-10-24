<template>
  <div class="d-flex flex-column">
    <div class="login-header"/>
    <div class="login-brand">
      {{ 'brand.argus'|trans }}
    </div>

    <div class="login-card">
      <ValidationObserver
          ref="validationObserver"
          v-slot="observer"
          slim>
        <form @submit.prevent="observer.handleSubmit(doLogin)">
          <LoadingOverlay :show="loggingIn">
            <b-card>
              <template #header>
                <font-awesome-icon
                    fixed-width
                    icon="sign-in-alt"/>
                {{ 'title.login'|trans }}
              </template>

              <ErrorAlert
                  :show="showLoginAlert"
                  :text="'auth.flash.auth-failed'|trans"/>

              <ValidatedField
                  :label="'auth.field.username'|trans"
                  rules="required|email">
                <b-input
                    ref="usernameField"
                    v-model="username"
                    autocomplete="username"
                    autofocus
                    trim
                    type="email"/>
              </ValidatedField>

              <ValidatedField
                  :label="'auth.field.password'|trans"
                  rules="required">
                <b-input
                    v-model="password"
                    autocomplete="current-password"
                    type="password"/>
              </ValidatedField>

              <div class="d-flex justify-content-end">
                <b-button
                    :disabled="loggingIn"
                    type="submit"
                    variant="primary">
                  <font-awesome-icon
                      fixed-width
                      icon="sign-in-alt"/>
                  {{ 'auth.button.login'|trans }}
                </b-button>
              </div>
            </b-card>
          </LoadingOverlay>
        </form>
      </ValidationObserver>
    </div>
  </div>
</template>

<script lang="ts">
  import {BFormInput} from 'bootstrap-vue';
  import {ValidationObserver} from 'vee-validate';
  import {Component, Ref, Vue} from 'vue-property-decorator';
  import ArgusAlert from '../components/alerts/ArgusAlert.vue';
  import ErrorAlert from '../components/alerts/ErrorAlert.vue';
  import ValidatedField from '../components/form/ValidatedField.vue';
  import LoadingOverlay from '../components/layout/LoadingOverlay.vue';

  @Component({
    components: {LoadingOverlay, ValidatedField, ErrorAlert, ArgusAlert},
  })
  export default class LoginPage extends Vue {
    protected loggingIn = false;
    protected showLoginAlert = false;

    protected username = '';
    protected password = '';

    @Ref()
    private readonly validationObserver!: InstanceType<typeof ValidationObserver>;

    @Ref()
    private readonly usernameField!: BFormInput;

    public async doLogin(): Promise<void> {
      if (this.loggingIn) {
        return;
      }

      this.loggingIn = true;

      try {
        await this.$http.post(this.$sfRouter.generate('auth'), {
          username: this.username,
          password: this.password,
        });
        this.$store.direct.commit.loggedIn();
      } catch (e) {
        this.password = '';
        this.showLoginAlert = true;
        this.loggingIn = false;
        await this.$nextTick();
        this.usernameField.focus();
        this.validationObserver.reset();
      }
    }
  }
</script>

<style lang="scss" scoped>
  @import "assets/css/variables";

  .login-header {
    background-image: url("../../img/argus_white.svg");
    background-repeat: no-repeat;
    background-position: center center;
    background-size: contain;
    width: 100%;
    height: 6rem;
    margin-top: 2rem;
  }

  .login-brand {
    text-align: center;
    font-size: larger;
    color: white;
    margin-top: 0.5rem;
    margin-bottom: 2rem;
    text-transform: uppercase;
  }

  .login-card {
    margin: auto;
    min-width: 100%;
    max-width: 100%;

    @include media-breakpoint-up(md) {
      min-width: map-get($grid-breakpoints, sm);
      max-width: map-get($grid-breakpoints, sm);
    }
  }
</style>
