jQuery(function($) {
    $(".assignmentselection").each(function() {

        var input = $(this);
        var container = $(this).closest(".control-group").parent();

        // Backwards compatibility fix
        fix = input.parent().hasClass("well-assign");
        if (fix) {
            container = $(this).closest(".well-assign");
            container.children(":last-child").addClass("assign-options");
            container.children().not(":last-child").wrapAll('<div class="control-group assignmentselection">');
            container.find(".assignmentselection label").wrap("<div class=\"control-label\"></div>")
            container.find(".assignmentselection .control-label").css({
                'padding-right' : '20px'
            })
        } else {
            container.children().not(":first-child").wrapAll('<div class="assign-options">');
            container.children().filter(":first-child").addClass("assignmentselection");
        }

        // Add missing class
        if (!container.hasClass("assign")) {
            container.addClass("assign");
        }

        // Setup Events
        input.on("change", function() {

            container.removeClass("alert-success alert-danger");

            // Joomla 4
            input.removeClass("custom-select-color-state custom-select-success custom-select-danger");

            if ($(this).val() > 0) {
                container.find(".assign-options").slideDown("fast");

                class_ = ($(this).val() == "1") ? "alert-success" : "alert-danger";
                container.addClass(class_);

                // Joomla 4
                input.addClass("custom-select-color-state").addClass(($(this).val() == "1") ? "custom-select-success" : "custom-select-danger");
                
            } else {
                container.find(".assign-options").slideUp("fast");
            }      
        }).trigger("change"); 
    })
})