<template>
  <div>
    <transition name="fade-fast">
      <div class="init-loader" v-if="loading">
        <font-awesome-icon icon="circle-notch" spin size="6x"/>
        <div class="loader-text">{{ 'brand.loading'|trans }}</div>
      </div>
    </transition>

    <transition name="fade">
      <div class="container" v-show="!loading">
        <transition name="router">
          <RouterView/>
        </transition>
      </div>
    </transition>
  </div>
</template>

<script lang="ts">
  import {Component, Vue} from 'vue-property-decorator';

  @Component
  export default class App extends Vue {
    public loading: boolean = true;

    public async mounted() {
      this.$nextTick(() => this.testAuthentication());
    }

    private async testAuthentication() {
      try {
        await this.$httpInstance.get(this.$sfRouter.generate('auth_test'));
        if (this.$route.name === 'login') {
          await this.$router.push({name: 'dashboard'});
        }
      } catch (e) {
        if (this.$route.name !== 'login') {
          await this.$router.push({name: 'login'});
        }
      } finally {
        this.loading = false;
      }
    }
  }
</script>
