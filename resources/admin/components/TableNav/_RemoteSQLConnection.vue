<template>
    <div class="sql_connection_wrapper nt-form-group">
        <el-row :gutter="20">
            <el-col :span="24">
                    <label class="nt-form-label">{{ $t('DB Name') }}</label>
                    <NinjaInput
                        v-model="connection.db_name"
                        :placeholder="$t('DB Name')"
                    />
            </el-col>
        </el-row>

        <el-row :gutter="20">
            <el-col :span="12">
                    <label class="nt-form-label">{{ $t('DB username') }}</label>
                    <NinjaInput
                        v-model="connection.db_username"
                        :placeholder="$t('DB username')"
                    />
            </el-col>

            <el-col :span="12">
                    <label class="nt-form-label">{{ $t('DB User Password') }}</label>
                    <NinjaInput
                        v-model="connection.db_userpassword"
                        :placeholder="$t('DB User Password')"
                    />
            </el-col>
        </el-row>

        <el-row :gutter="20">
            <el-col :span="12">
                <div class="nt-form-group">
                    <label class="nt-form-label">{{ $t('DB Host') }}</label>
                    <NinjaInput
                        v-model="connection.db_host"
                        :placeholder="$t('DB Host')"
                    />
                </div>
            </el-col>
            <el-col :span="12">
                <div class="nt-form-group">
                    <label class="nt-form-label">{{ $t('DB Host Port') }}</label>
                    <NinjaInput
                        v-model="connection.db_host_port"
                        :placeholder="$t('DB Host Port')"
                    />
                </div>
            </el-col>
        </el-row>
    </div>

</template>

<script type="text/babel">
import NinjaInput from "../../@ui-utils/NinjaInput.vue";

export default {
    name: 'remote_sql_form',
    components: {NinjaInput},
    props: ['connection'],
    data() {
        return {}
    }
}
</script>
