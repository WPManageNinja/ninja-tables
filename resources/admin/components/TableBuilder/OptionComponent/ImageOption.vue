<template>
    <div class="component-wrapper">
        <el-button size="medium" type="primary" @click.prevent="replaceImage" round icon="el-icon-upload">
            {{ $t('Replace Image') }}
        </el-button>
        <alignment :label="$t('Alignment')" v-model="item.data.style.alignment"></alignment>
        <slider-input :label="$t('Image Size')" v-model="item.data.style.size" :max="100"
                      :min="10" :step="1">
        </slider-input>
        <shape
            :label="$t('Image Shape')"
            v-model="item.data.style.shape"
        ></shape>
        <text-input :label="$t('Image Alternative Text')" v-model="item.data.style.alt"></text-input>
        <text-input :label="$t('Image Link')" v-model="item.data.style.link"></text-input>
        <switch-input :label="$t('Open in new tab')" v-model="item.data.style.target"></switch-input>
        <checkbox
            :label="$t('Link rel attributes')"
            :options="linkOptions"
            v-model="item.data.style.linkAttributes"
        >
        </checkbox>
    </div>
</template>

<script>
import Alignment from "../SettingComponent/Alignment";
import TextInput from "../SettingComponent/TextInput";
import SliderInput from "../SettingComponent/SliderInput";
import Shape from "../SettingComponent/Shape"
import Checkbox from "../SettingComponent/CheckboxInput";
import SwitchInput from "../SettingComponent/SwitchInput.vue";

export default {
    name: "ImageOption",
    props: ["item"],
    data() {
        return {
            linkOptions: [
                {label: 'sponsored', value: 'sponsored'},
                {label: 'nofollow', value: 'nofollow'},
                {label: 'noreferrer', value: 'noreferrer'},
            ]
        };
    },
    components: {
        SwitchInput,
        Alignment,
        TextInput,
        SliderInput,
        Shape,
        Checkbox
    },
    methods: {
        replaceImage() {
            const upload = wp
                .media({
                    title: "Choose Image", //Title for Media Box
                    multiple: false, //For limiting multiple image
                })
                .on("select", () => {
                    const select = upload.state().get("selection");
                    const attach = select.first().toJSON()
                    this.item.data.value = attach.url;
                })
                .open();
        }
    }
};
</script>
