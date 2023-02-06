<template>
    <div class="ninja_intro_welcome">
        <h2>Welcome to Ninja Tables</h2>
        <p>Thank you for installing Ninja Tables - Best Responsive Table Plugin for WordPress</p>
        <div class="ninja_actions">
            <el-button type="success" @click="create">
                Create Your First Table
            </el-button>
            <router-link :to="{ name: 'import_tables' }">
                <el-button type="info">{{ $t( 'Import From CSV' ) }}</el-button>
            </router-link>
        </div>
        <hr/>
        <div class="ninja_docs">
            <h4>Ninja Tables Documentation:</h4>
            <ul>
                <li>
                    <a target="_blank"
                       href="https://wpmanageninja.com/ninja-tables/use-case/?ninja_intro=1">
                        Demo and Basic Settings
                    </a>
                </li>
                <li>
                    <a target="_blank"
                       href="https://wpmanageninja.com/docs/ninja-tables/setting-up-a-table/?ninja_intro=1">
                        Setting Up a Table
                    </a>
                </li>
                <li>
                    <a target="_blank"
                       href="https://wpmanageninja.com/docs/ninja-tables/configure-responsive-breakdowns-for-table/?ninja_intro=1">
                        Make Your Table Looks Great on All Devices
                    </a>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
    export default {
        name: "Welcome",

        methods: {
            create() {
                this.$emit('create', true);
            }
        }
    }
</script>

<style lang="scss">
    .ninja_intro_welcome {
        max-width: 600px;
        margin: 45px auto 0px;
        padding: 30px 20px;
        background: white;
        text-align: center;

        h2 {
            font-size: 30px;
        }

        .ninja_actions {
            margin-bottom: 30px;
        }

        .ninja_docs {
            text-align: left;
        }
    }
</style>
