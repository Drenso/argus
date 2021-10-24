<template>
  <div>
    <transition name="fade">
      <div
          v-if="loading"
          class="init-loader">
        <font-awesome-icon
            icon="circle-notch"
            size="6x"
            spin/>
        <div class="loader-text">
          {{ 'brand.loading'|trans }}
        </div>
      </div>
    </transition>

    <b-overlay
        class="app-overlay"
        :show="$store.direct.state.isWorking"
        variant="dark">
      <template #overlay>
        <LoadingOverlayIcon class="text-white"/>
      </template>

      <transition name="router-fade">
        <div v-if="!loading">
          <transition name="router-fade">
            <LoginPage
                v-if="!$store.direct.state.isAuthenticated"
                key="login"/>

            <div
                v-else
                key="content">
              <Menu/>

              <div class="container">
                <div class="content">
                  <transition name="router-fade">
                    <RouterView/>
                  </transition>
                </div>
              </div>
            </div>
          </transition>
        </div>
      </transition>
    </b-overlay>
  </div>
</template>

<script lang="ts">
  import {Component, Vue} from 'vue-property-decorator';
  import LoadingOverlayIcon from './components/layout/LoadingOverlayIcon.vue';
  import Menu from './components/layout/Menu.vue';
  import LoginPage from './pages/LoginPage.vue';

  @Component({
    components: {LoadingOverlayIcon, LoginPage, Menu},
  })
  export default class App extends Vue {
    public loading = true;

    public mounted(): void {
      this.$nextTick(() => this.testAuthentication());
    }

    private async testAuthentication(): Promise<void> {
      try {
        await this.$httpInstance.get(this.$sfRouter.generate('auth_test'));
        this.$store.direct.commit.loggedIn();
      } catch (e) {
        // Ignore errors
      } finally {
        this.loading = false;
      }
    }
  }
</script>

<style scoped lang="scss">
  .app-overlay {
    /deep/ {
      > .b-overlay {
        min-height: 100vh;
        z-index: 1050 !important;
      }
    }
  }
</style>
