<?php

return [

	'summary_view' => [

        /*
        |--------------------------------------------------------------------------
        | Focus Fields
        |--------------------------------------------------------------------------
        |
        | Some listing summary styles are designed with a dedicated area to focus
        | a field by surrounding them with key lines or highlight them with bold
        | text. For example, in a real estate directory, you may want to choose
        | 'Price' as the focus field; or in a web directory, you can choose
        | 'Website'.
        |
        */

        'focus_field_1' => $this->config->getTemParam('focusField1','16'),

        'focus_field_2' => $this->config->getTemParam('focusField2',''),

        /*
        |--------------------------------------------------------------------------
        | Hidden Fields
        |--------------------------------------------------------------------------
        |
        | Specify the field IDs of the fields that you don't want to display in
        | summary view. This is useful when you customized your MT template to
        | display a field(s) and don't want it to show again in summary view.
        |
        */

        'hide_fields' => []

	],

	'details_view' => [

        /*
        |--------------------------------------------------------------------------
        | Image Max Width
        |--------------------------------------------------------------------------
        |
        | This controls the width of your image in details view when it is
        | showing only one image. Setting this to true will set the image width
        | to 100%.
        |
        */

        'only_one_image' => [
				'max_width' => true
        ],


        /*
       |--------------------------------------------------------------------------
       | Image Gallery Width
       |--------------------------------------------------------------------------
       |
       | This controls the width of your image gallery in details view. Setting
       | this to true will set the image gallery's width to 100%.
       |
       */

        'image_gallery' => [
            'max_width' => true,
        ]

	]


];