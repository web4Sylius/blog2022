/**
  * SAB
  * (c) WebFactory Ltd, 2016 - 2021
  */

jQuery(document).ready(function($){
    if (typeof sab_pointers != "undefined") {
        $.each(sab_pointers, function (index, pointer) {
          if (index.charAt(0) == "_") {
            return true;
          }
          $(pointer.target)
            .pointer({
              content: "<h3>" + sab_pointers.plugin_name + "</h3><p>" + pointer.content + "</p>",
              pointerWidth: 380,
              position: {
                edge: pointer.edge,
                align: pointer.align,
              },
              close: function () {
                $.get(ajaxurl, {
                  _ajax_nonce: sab_pointers._nonce_dismiss_pointer,
                  action: "sab_dismiss_pointer",
                });
              },
            })
            .pointer("open");
        });
      }

  });
