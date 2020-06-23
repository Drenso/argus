<template>
  <ValidationProvider tag="div" class="validated-field" :debounce="100" :rules="rules" v-slot="{ errors }">
    <b-form-group class="mb-0" :label="label" :id="id" :state="errors.length > 0 ? false : null">

      <template #invalid-feedback>
        <slot name="messages" :errors="errors" :icon="icon" :help="help">
          <b-form-invalid-feedback :state="false" v-for="error in errors" :key="error">
            <TextWithLeftIcon :icon="icon">{{ error }}</TextWithLeftIcon>
          </b-form-invalid-feedback>
        </slot>
      </template>

      <slot :state="errors.length > 0 ? false : null"/>
      <b-form-text v-if="!!help">
        <TextWithLeftIcon icon="info-circle">{{ help }}</TextWithLeftIcon>
      </b-form-text>

    </b-form-group>
  </ValidationProvider>
</template>

<script lang="ts">
  import {Component, Prop, Vue} from 'vue-property-decorator';
  import TextWithLeftIcon from '../icon/TextWithLeftIcon.vue';

  @Component({
    components: {TextWithLeftIcon},
    inheritAttrs: false,
  })
  export default class ValidatedField extends Vue {
    @Prop({required: true, type: String})
    public readonly label!: string;

    @Prop({default: null})
    public readonly help!: string | null;

    @Prop({type: String, default: 'exclamation-triangle'})
    public readonly icon!: string;

    @Prop()
    public readonly rules!: any | undefined;

    protected readonly id: string = this.$random();
  }
</script>

<style scoped lang="scss">
  @import 'assets/css/variables';

  .validated-field {
    margin-bottom:  map_get($spacers, 3);
  }
</style>
