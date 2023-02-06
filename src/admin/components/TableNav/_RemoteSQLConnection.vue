<template>
    <div class="sql_connection_wrapper">
        <div class="form-group">
            <label>
                DB Name
                <el-input size="mini" placeholder="DB Name" v-model="connection.db_name"></el-input>
            </label>
        </div>
        <div class="nt_row_2">
            <div class="form-group">
                <label>
                    DB username
                    <el-input size="mini"  placeholder="DB User Name" v-model="connection.db_username"></el-input>
                </label>
            </div>
            <div class="form-group">
                <label>
                    DB User Password
                    <el-input size="mini"  type="password" placeholder="DB User Password" v-model="connection.db_userpassword"></el-input>
                </label>
            </div>
        </div>

        <div class="nt_row_2">
            <div class="form-group">
                <label>
                    DB Host
                    <el-input size="mini"  placeholder="DB Host" v-model="connection.db_host"></el-input>
                </label>
            </div>
            <div class="form-group">
                <label>
                    DB Host Port
                    <el-input size="mini"  type="number" placeholder="Default: 3306 " v-model="connection.db_host_port"></el-input>
                </label>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
    export default {
        name: 'remote_sql_form',
        props: ['connection'],
        data() {
            return {

            }
        }
    }
</script>