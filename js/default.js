/**
 * js/default.js
 * Tạo bởi Player_Nguyen
 *
 * Lưu ý: không được chỉnh sửa bất kì thứ gì nếu như bạn không biết
 * vì nó có thể làm gián đoạn cả trang chủ của bạn trong phần này
 *
 * Bạn có thể chỉnh sửa nếu bạn là một người biết về JavScript
 */


/**
 *  Constants
 */
const ANIMATION_MILLI_SECOND = 1000;
const MOBILE_MAX_WIDTH = 768;

/**
 *  Variables
 */

let is_mobile;
let screen_width;

/**
 * Is mobile mode or not
 */
function mobile_init() {
    screen_width = $(document).width();

    is_mobile = screen_width <= MOBILE_MAX_WIDTH;
    $(window).resize(() => {
        screen_width = $(document).width();
        is_mobile = screen_width <= MOBILE_MAX_WIDTH;
    });
}

/**
 * On the document was loaded
 */
$(document).ready(() => {
    mobile_init();
    dismiss_init();
    navbar_mobile_init();
    editor_init();
    checkbox_init()
});

/**
 * Adding event of dismiss button for alert
 */
function dismiss_init() {
    let dismiss = $('.alert-dismiss');
    dismiss.click(() => {
        let _parent = dismiss.parent();
        if (_parent != null) _parent.slideUp(ANIMATION_MILLI_SECOND / 3);
    });
}

/**
 * Got a random string
 * @param len
 * @param prefix
 * @param sufix
 * @returns {string}
 */
function rand_string(len, prefix = '', sufix = '') {
    let chars = "ABCDEFGHIJKLMNOPSWXYZabdefghijklmnopswxyz1234567890_-?{}[]";
    let build = prefix + "";
    for (let i = 0; i < len; i++) {
        build += chars[rand_integer(chars.length)];
    }
    if (sufix === '') build += sufix;
    return build;
}

/**
 * Add mobile process navbar
 */
function navbar_mobile_init() {
    /**
     * Collapse init
     */
    let collapser = $(".navbar-collapse");
    let collapsible = collapser.parent().parent();
    let contents = collapsible.find(".navbar-content");
    if (!collapsible.hasClass("collapsible")) {
        console.warn("Đối tượng " + collapsible.html() + " không có class=collapsible")
    }
    collapser.click(() => {
        if ($(contents).is(":hidden")) show_navbar_contents();
        else hide_navbar_contents();
    });
    /**
     * Set the collapser when resize
     */
    $(window).resize((e) => {
        if (!is_mobile) {
            if ($(contents).is(":hidden")) show_navbar_contents();
        } else {
            if (!($(contents).is(":hidden"))) hide_navbar_contents();
        }
    });

    let old_parent;

    /**
     * Load dropdown
     */
    let dropdown_contents = $('.dropdown-content');
    for (let i = 0; i < dropdown_contents.length; i++) {
        /**
         * Defining
         */
        let contents = $(dropdown_contents[i]);
        let parent = $(dropdown_contents[i]).parent();
        /**
         * Style and accessibility
         */
        contents.css("z-index", '1');
        parent.attr("tabIndex", 0);
        /**
         * On focus & on click
         */
        parent.hover(() => {
            contents.show(ANIMATION_MILLI_SECOND / 4);
        }, ()=> {
            contents.hide(ANIMATION_MILLI_SECOND / 4);
        });
        parent.keydown ((e)=> {
            contents.show(ANIMATION_MILLI_SECOND / 4);
            if (old_parent != null && e.currentTarget !== old_parent)  {
                $(old_parent).children(".dropdown-content").hide(ANIMATION_MILLI_SECOND / 4);
            }
            old_parent = e.currentTarget
        });
    }

    function show_navbar_contents() {
        contents.show(ANIMATION_MILLI_SECOND / 4);
    }

    function hide_navbar_contents() {
        contents.hide(ANIMATION_MILLI_SECOND / 4);
    }
}

/**
 * Got a random integer
 * @param max
 * @returns {number}
 */
function rand_integer(max) {
    return Math.floor(Math.random() * Math.floor(max));
}

/**
 * Editor initial
 */
function editor_init() {
    let editable = $(".lauth-editor");
    let editable_parent = editable.parent();

    let editable_height = editable.innerHeight();

    editable.attr('contenteditable', "true");

    editable_parent.append("<input type='hidden' name='" + editable.attr('name') + "' value='"+editable.html()+"' id='" + editable.attr('name') + "'>")

    let hidden_input = $('#' +  editable.attr('name'));

    editable.keyup((e) => {

    });
    editable.blur((e) => {
        let builder = editable.html().replace(/&lt;/g, "<").replace(/&gt;/g, ">");
        editable.html(builder);
        hidden_input.val(builder);
    });
}

/**
 * Checkbox initial
 */
function checkbox_init() {
    let checkboxers = $("input[type=checkbox]");
    /**
     * Loading first
     */
    checkboxers.ready(() => {
        if  (checkboxers.val() === "1") { checkboxers.attr("checked", "checked"); }
    });
    /**
     * Return to right value
     */
    checkboxers.click((e) => {
        let _target = e.target;
        if (_target.checked) _target.value = "1";
        else _target.value = "0"
    });
}

/**
 * Copying the string
 * @param string
 */
function copy(string) {
    let _temp = $('<input contenteditable="true" readonly="false"/>');
    $("body").append(_temp);

    _temp.val(string).select();

    let _execute = document.execCommand("copy");
    if (_execute) {
        console.log("Copied " + string);
        alert("Đã sao chép " + string);
    }
    else {
        alert("Đã có lỗi khi sao chép " + string);
        console.error("Cannot copy " + string);
    }

    _temp.remove();


}