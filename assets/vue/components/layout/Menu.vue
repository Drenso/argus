<template>
  <b-navbar type="dark" variant="primary" sticky>
    <b-navbar-brand :to="{name: 'dashboard'}">
      <img src="../../../img/argus_white.svg" width="30" height="30"
           class="d-inline-block align-top mr-2" :alt="'brand.argus'|uppercase">
      {{ 'brand.argus'|trans|uppercase }}
    </b-navbar-brand>

    <b-navbar-nav class="ml-auto">
      <b-button variant="light" @click="logout" :disabled="logoutPending">
        <font-awesome-icon :icon="logoutPending ? 'circle-notch' : 'sign-out-alt'" fixed-width :spin="logoutPending"/>
        {{ 'auth.button.logout'|trans }}
      </b-button>
    </b-navbar-nav>
  </b-navbar>
</template>

<script lang="ts">
  import {Component, Vue} from 'vue-property-decorator';

  @Component
  export default class Menu extends Vue {
    public logoutPending: boolean = false;

    public async logout() {
      this.logoutPending = true;
      this.$store.direct.commit.startWork();
      await this.$http.delete(this.$sfRouter.generate('auth_clear'));
      this.$store.direct.commit.loggedOut();
      this.$store.direct.commit.endWork();
    }
  }
</script>
