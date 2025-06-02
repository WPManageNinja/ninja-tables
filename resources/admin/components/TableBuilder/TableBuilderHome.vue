<template>
  <div class="ninja-table-dg-wrapper">
    <div class="ninja_main_nav !h-[70px]">
      <top-nav :initialData="initialData" :selectedDevice="selectedDevice" @deviceSelected="deviceSelected"
        :tableId="$route.params.table_id">
      </top-nav>
    </div>
      <Notices/>
    <el-row :gutter="20">
      <el-col :xs="24" :sm="10" :md="9" :lg="8" id="leftside">
        <LeftSideBar :singleItem="singleItem" :initialData="initialData" :selectedDevice="selectedDevice"
          @deviceSelected="deviceSelected"></LeftSideBar>
      </el-col>
      <el-col :xs="24" :sm="14" :md="15" :lg="16">
        <RightSideBar v-if="initialData" @editItem="editItem" :table="initialData.table"
          :selectedDevice="selectedDevice" :initialData="initialData" :tableId="$route.params.table_id"
          style="height: auto; padding-bottom: 25px;">
        </RightSideBar>
      </el-col>
    </el-row>
  </div>
</template>
<script>
import TopNav from "./TopNav.vue";
import LeftSideBar from "./Sidebar/LeftSideBar.vue";
import RightSideBar from "./Sidebar/RightSideBar.vue";
import { useEventBus } from '../../../admin/eventBus';
import Notices from "../../@ui-utils/Notices.vue";

export default {
  name: "TableBuilderHome",
  data() {
    return {
      bus : useEventBus(),
      tableId: '',
      initialData: {},
      singleItem: {},
      selectedDevice: '',
      changeSomething: false
    };
  },
  components: {
      Notices,
    TopNav,
    LeftSideBar,
    RightSideBar
  },
  methods: {
    deviceSelected(data) {
      this.selectedDevice = data;
    },
    editItem(singleItem) {
      this.singleItem = singleItem
    },
    addOrEditTable() {
      this.$get(`table-builder/${this.$route.params.table_id}`, {
        id: this.$route.params.table_id,
      })
        .then(response => {
          this.initialData = response.data;
        })
        .catch(error => {
          this.$message({
            showClose: true,
            message: this.$t('Something went wrong, please try again.'),
            type: 'warning'
          });
        });
    },
  },
  mounted() {
    this.clipboard();
    this.addOrEditTable();
  },
  beforeRouteLeave(to, from, next) {
    if (this.changeSomething) {
      const answer = window.confirm('Changes that you made may not be saved');
      if (answer) {
        next();
        window.onbeforeunload = null;
      } else {
        next(false);
      }
    } else {
      next();
    }
  },
  created() {
    this.bus.on("somethingChanged", () => {
      this.changeSomething = true;
      window.onbeforeunload = function () {
        return true;
      }
    });

    this.bus.on("saveData", () => {
      this.changeSomething = false;
      window.onbeforeunload = null;
    });
  },
};
</script>
