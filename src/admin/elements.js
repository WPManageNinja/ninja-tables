import Vue from 'vue';

import {
    Button,
    Table,
    TableColumn,
    Dialog,
    Popover,
    Loading,
    Message,
    MessageBox,
    Icon,
    Tooltip,
    Pagination,
    Collapse,
    CollapseItem,
    Container,
    Aside,
    Main,
    Menu,
    MenuItem,
    Header,
    ColorPicker,
    Form,
    FormItem,
    Input,
    Checkbox,
    RadioGroup,
    Radio,
    Select,
    Option,
    OptionGroup,
    Switch,
    CheckboxGroup,
    RadioButton,
    TabPane,
    Tabs,
    Steps,
    Step,
    Alert,
    Row,
    Col,
    Transfer,
    Upload,
    DatePicker,
// Below files are used in table builder
    InputNumber,
    Slider,
    Card,
    Rate,
    Progress,
    ButtonGroup,
    Dropdown,
    DropdownMenu,
    DropdownItem,
    Avatar,
    Badge,
    Tag
} from 'element-ui';

Vue.use(Button);
Vue.use(Upload);
Vue.use(DatePicker);
Vue.use(Table);
Vue.use(ColorPicker);
Vue.use(Pagination);
Vue.use(TableColumn);
Vue.use(Popover);
Vue.use(Menu);
Vue.use(Header);
Vue.use(MenuItem);
Vue.use(Loading);
Vue.use(Icon);
Vue.use(Tooltip);
Vue.use(Container);
Vue.use(Aside);
Vue.use(Main);
Vue.use(Collapse);
Vue.use(CollapseItem);
Vue.use(Dialog);
Vue.use(Form);
Vue.use(FormItem);
Vue.use(Input);
Vue.use(Select);
Vue.use(Option);
Vue.use(OptionGroup);
Vue.use(Checkbox);
Vue.use(RadioGroup);
Vue.use(Radio);
Vue.use(RadioButton);
Vue.use(Switch);
Vue.use(CheckboxGroup);
Vue.use(Tabs);
Vue.use(TabPane);
Vue.use(Steps);
Vue.use(Step);
Vue.use(Alert);
Vue.use(Row);
Vue.use(Col);
Vue.use(Transfer);
// Below files are used in table builder
Vue.use(Slider);
Vue.use(Card);
Vue.use(InputNumber);
Vue.use(Rate);
Vue.use(Progress);
Vue.use(ButtonGroup);
Vue.use(Dropdown);
Vue.use(DropdownMenu);
Vue.use(DropdownItem);
Vue.use(Avatar);
Vue.use(Badge);
Vue.use(Tag);

Vue.prototype.$message = Message;
Vue.prototype.$msgbox = MessageBox;
Vue.prototype.$alert = MessageBox.alert;
Vue.prototype.$confirm = MessageBox.confirm;
Vue.prototype.$prompt = MessageBox.prompt;

locale.use(lang);
import lang from 'element-ui/lib/locale/lang/en';
import locale from 'element-ui/lib/locale';

export default Vue;