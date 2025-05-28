<template>
    <button
        :disabled="disabled"
        :loading="loading"
        class="ninja-button flex items-center justify-center"
        :class="{
            'ninja-button--primary': type === 'primary' && !disabled,
            'ninja-button--secondary': type === 'secondary' && !disabled,
            'ninja-button--danger': type === 'danger' && !disabled,
            'ninja-button--info': type === 'info' && !disabled,
            'ninja-button--disabled': disabled,
            'ninja-button--small': size === 'small',
            'ninja-button--large': size === 'large',
            'ninja-button--default': size === 'default',
            'ninja-button--pro': type === 'pro' && !disabled,
        }"
    >
        <div v-if="loading" class="ninja-button-spinner mr-2"> </div>
        <img v-else-if="icon" :src="icon" :width="iconSize" :height="iconSize" class="mr-1" />
        <template v-if="btnText">
            {{ btnText }}
        </template>
        <slot v-else></slot>
    </button>
</template>

<script>
export default {
    name: "NinjaButton",
    props: {
        type: {
            type: String,
            default: "primary",
        },
        size: {
            type: String,
            default: "default",
        },
        icon: {
            type: String,
            default: null,
        },
        iconSize: {
            type: String,
            default: "18px",
        },
        disabled: {
            type: Boolean,
            default: false,
        },
        loading: {
            type: Boolean,
            default: false,
        },
        btnText: {
            type: String,
            default: '',
        }
    }
};
</script>

<style lang="scss" scoped>
  .ninja-button {
    @apply rounded-[8px] leading-5 font-[500] text-[14px] cursor-pointer flex;
  }
  .ninja-button--primary {
    @apply bg-[#335cff] text-white hover:bg-[#2547D0] delay-100 duration-100;
  }
  .ninja-button--secondary {
    @apply bg-white text-[#525866] border border-solid border-[#E1E4EA];
    &:hover {
      @apply bg-[#F5F6F7] delay-100 duration-100;
    }
  }
  .ninja-button--danger {
    @apply bg-white text-[#FB3748] border border-solid border-[#FB3748];
    &:hover {
      @apply bg-[#FCEBE6] delay-100 duration-100;
    }
  }
  .ninja-button--info {
    @apply bg-white text-[#525866] border border-solid border-[#E1E4EA];
  }

  .ninja-button--disabled {
    @apply bg-[#E9ECEF] text-[#ADB5BD] border border-solid border-[#DEE2E6] cursor-not-allowed hover:bg-[#E9ECEF];
  }

  .ninja-button--disabled.pro-component {
    @apply bg-white text-[#525866] border border-solid border-[#E1E4EA];
    &:hover {
      @apply bg-[#F5F6F7] delay-100 duration-100;
    }
  }

  .ninja-button--pro {
      @apply bg-[#0E121B] hover:bg-[#222530] text-white delay-100 duration-100;
  }
  
  /* Size variations */
  .ninja-button--small {
    @apply p-[4px] px-3 text-[12px];
  }
  .ninja-button--default {
    @apply p-[8px] px-5 text-[14px];
  }
  .ninja-button--large {
    @apply p-[12px] px-7 text-[16px];
  }
  .ninja-button-spinner{
    @apply w-4 h-4 border-2 border-solid border-t-transparent rounded-full animate-spin;
    border-color: currentColor;
    border-top-color: transparent;
  }
</style>
