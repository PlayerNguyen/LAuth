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
    /**
     * Load dropdown
     */
    let dropdown_contents = $('.dropdown-content');
    for (let i = 0; i < dropdown_contents.length; i++) {
        let contents = $(dropdown_contents[i]);
        let parent = $(dropdown_contents[i]).parent();
        let children = $(dropdown_contents[i]).children();
        /**
         * Parents
         */
        parent.hover(() => {
            contents.slideDown(ANIMATION_MILLI_SECOND / 5);
        }, () => {
            contents.slideUp(ANIMATION_MILLI_SECOND / 5);
        });
        /**
         * Set focusable
         */
        parent.attr("tabindex", 0);
        $("*").focus((e) => {

            if (e.target === parent[0] || e.relatedTarget === parent[0]) {
                contents.slideDown(ANIMATION_MILLI_SECOND / 5);
            } else {
                contents.slideUp(ANIMATION_MILLI_SECOND / 5);
            }
        });
        parent.focusout((e) => {
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
 * Dùng để sinh ngẫu nhiên một chuỗi
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
 * Ngẫu nhiên một số nguyên
 * @param max
 * @returns {number}
 */
function rand_integer(max) {
    return Math.floor(Math.random() * Math.floor(max));
}

