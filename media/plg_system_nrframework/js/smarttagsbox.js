/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2018 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

jQuery(function($) {
    var $el = $(".st")
        $el_box = $el.find(".box")
        $el_nav  = $el.find(".st_nav")
        $el_tabs = $el.find(".st_tabs")
        $el_search = $el.find(".st_input_search")
        $active_element =  null
        input_selector = Joomla.getOptions('SmartTagsBox').selector;

    attachicons();

    $el.on("update", function() {
        attachicons();
    })

    // Search input event
    $el_search.on("keyup", function() {
        search($(this).val());
    })

    // Icon click event
    $(document).on("click", "span.st_trigger", function() {
        showBox($(this));
        return false;
    });

    $(document).on("click", ".st_nav a", function() {
        showTab($(this).index());
        return false;
    });

    $(document).on("click", ".st_tabs a", function() {
        selectTag($(this).data("key"));
        return false;
    });

    $(document).on("click", ".st_overlay", function() {
        hideBox();
        return false;
    });

    function attachicons() {
        $(input_selector).each(function(key) {
            var $input = $(this);

            // Skip if it has already the icon attached
            if ($input.next(".st_trigger").length == 1) {
                return true;
            }

            $input.parent().addClass("has-smarttags");
            $input.parent().append('<span title="' + Joomla.JText._('NR_SMARTTAGS_SHOW') + '" class="icon-tags st_trigger" data-id="' + key + '"></span>');

            // Add Helper Classes
            input_type = $input.prop('nodeName').toLowerCase();
            $input.parent().addClass("is_" + input_type);
        })
    }

    function search(filter) {
        $el_tabs.find(".st_search").remove();

        // If no string is passed then switch back to the first tab content
        if (filter.length == 0) {
            showTab();
        }
        
        var filter = $.trim(filter.toLowerCase());
        var found  = [];
      
        $el_tabs.find(".st_tab_content:not(.st_nosearch) > .st_item").each(function() {
            var item = $(this),
                text = $.trim(item.text().toLowerCase());

            if (text.indexOf(filter) > -1) {
                var html = item.clone().wrap('<p>').parent().html();
                found.push(html);
            }
        })

        $el_tabs.append('<div class="st_tab_content st_search st_nosearch"></div>');
        $el_tab_search = $el_tabs.find(".st_search");
        
        if (found.length == 0) {
            $el_tab_search.html(Joomla.JText._('NR_SMARTTAGS_NOTFOUND'));
        } else {
            $el_tab_search.html(found.join(''));
        }

        showTab($el_tab_search.index());
    }

    function selectTag(tag) {
        var $active_input = $active_element.prev();
        var isTinyMCE     = $active_element.parent().find(".mce-tinymce").length;

        if (isTinyMCE) {
            var editor = tinymce.get($active_input.attr("id"));
            editor.execCommand('mceInsertContent', false, " " + tag);
            return;
        }
        
        var input_value = $.trim($active_input.val() + " " + tag);
        $active_input.val(input_value).trigger("change");
    }

    function showTab(index) {
        // In case no index passed, use the 1st available
        var index = (index === undefined) ? 0 : index;

        // Show tab
        $el_tabs.find(".st_tab_content").hide().eq(index).show();

        // Make nav item active
        $el_nav.find("a").removeClass("active").eq(index).addClass("active");
    }

    function hideBox() {
        $(".has-smarttags").removeClass("active");
        $("body").removeClass("smarttags-active");
        $el.hide();
    }

    function showBox(element) {
        if ($el.is(":visible")) {
            hideBox();
            return;
        }

        $active_element = element;
        renderTags();

        var $container = element.parent()
            container_height = $container.outerHeight();

        // Add helper classes
        $container.addClass("active");
        $("body").addClass("smarttags-active");

        // Determine if the box should be displayed above or below the container
        var visible_height = $(window).height();
        var remain_height  = (visible_height - ($container.offset().top + $container.height()));
        var minimum_height_needed = 350;
        var position = (remain_height > minimum_height_needed) ? "bottom" : "top";
        var top = (position == "bottom") ? $container.offset().top + container_height : $container.offset().top - minimum_height_needed;

        $el.find(".st_box").css({
            "top": top + "px",
            "left": $container.offset().left + "px",
        }).end().show();

        showTab();
    }

    function renderTags() {
        // Render Tags
        var tags_default = Joomla.getOptions('SmartTagsBox').tags;
                
        tags = $.extend({}, tags_default); // Swallow clone object
        $(document).trigger("smartTagsBoxBeforeRender", [tags, $active_element]);

        var nav_html = '';
        $.each(tags, function(key) {
            nav_html += `<a href="#">${key}</a>`;
        });
        $el.find(".st_container .st_nav").html(nav_html);

        var content_html = '';
        $.each(tags, function(section_index, section) {
            content_html += `<div class="st_tab_content" data-key="${section_index}">`;
            $.each(section, function(tag_key, tag_value) {
                if (!tag_value) {
                    return true;
                }
                content_html += `<a class="st_item" data-key="${tag_key}">${tag_value} <small>${tag_key}</small>`;
            });
            content_html += '</div>';
        });

        $el.find(".st_container .st_tabs").html(content_html);
    }
})

