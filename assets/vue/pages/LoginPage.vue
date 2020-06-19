<template>
  <div class="d-flex flex-column">
    <div class="login-header"></div>
    <div class="login-brand">{{ 'brand.argus'|trans }}</div>

    <div class="login-card">
      <ValidationObserver slim ref="validationObserver" v-slot="observer">
        <form @submit.prevent="observer.handleSubmit(doLogin)">
          <b-overlay :show="loggingIn" rounded="sm">
            <template #overlay>
              <div class="text-center">
                <font-awesome-icon icon="circle-notch" spin size="4x"/>
              </div>
            </template>

            <b-card>
              <template #header>
                <font-awesome-icon icon="sign-in-alt" fixed-width/>
                {{ 'title.login'|trans }}
              </template>

              <ErrorAlert :show="showLoginAlert" variant="danger" :text="'auth.flash.auth-failed'|trans"/>

              <ValidatedField rules="required|email" :label="'auth.field.username'|trans">
                <b-input ref="usernameField" type="email" v-model="username" autofocus autocomplete="username" trim/>
              </ValidatedField>

              <ValidatedField rules="required" :label="'auth.field.password'|trans">
                <b-input v-model="password" type="password" autocomplete="current-password"/>
              </ValidatedField>

              <div class="d-flex justify-content-end">
                <b-button type="submit" variant="primary" :disabled="loggingIn">
                  <font-awesome-icon icon="sign-in-alt" fixed-width/>
                  {{ 'auth.button.login'|trans }}
                </b-button>
              </div>
            </b-card>
          </b-overlay>
        </form>
      </ValidationObserver>
    </div>
  </div>
</template>

<script lang="ts">
  import {Component, Ref, Vue} from 'vue-property-decorator';
  import ArgusAlert from '../components/alerts/ArgusAlert.vue';
  import ErrorAlert from '../components/alerts/ErrorAlert.vue';
  import {BFormInput} from 'bootstrap-vue';
  import ValidatedField from '../components/form/ValidatedField.vue';
  import {ValidationObserver} from 'vee-validate';

  @Component({
    components: {ValidatedField, ErrorAlert, ArgusAlert},
  })
  export default class LoginPage extends Vue {
    protected loggingIn: boolean = false;
    protected showLoginAlert: boolean = false;

    protected username: string = '';
    protected password: string = '';

    @Ref()
    private readonly validationObserver!: InstanceType<typeof ValidationObserver>;

    @Ref()
    private readonly usernameField!: BFormInput;

    public async doLogin() {
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
