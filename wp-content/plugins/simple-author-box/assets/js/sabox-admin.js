/**
 * SAB
 * (c) WebFactory Ltd, 2016 - 2021
 */

(function ($) {
  var context = $("#sabox-container");
  context.find(".saboxfield").on("change", function () {
    var show_tabs = false;
    if ($("#sab_show_custom_html").is(":checked")) {
      show_tabs = true;
      $('[data-tab="other"]').show();
    } else {
      $('[data-tab="other"]').hide();
    }

    if ($("#sab_show_latest_posts").is(":checked")) {
      show_tabs = true;
      $('[data-tab="latest_posts"]').show();
    } else {
      $('[data-tab="latest_posts"]').hide();
    }

    if (show_tabs == true) {
      $('[data-tab="about"]').show();
    } else {
      $('[data-tab="about"]').hide();
    }

    var value = getElementValue($(this));
    var elements = context.find(".show_if_" + $(this).attr("id"));

    if (value && "0" != value) {
      elements.show(300);
    } else {
      elements.hide(250);
    }
  });

  function getElementValue($element) {
    var type = $element.attr("type");
    var name = $element.attr("name");

    if ("checkbox" === type) {
      if ($element.is(":checked")) {
        return 1;
      } else {
        return 0;
      }
    } else {
      return $element.val();
    }
  }

  function adminTabSwitching() {
    var navTabSelector = ".nav-tab-wrapper .epfw-tab:not( .epfw-tab-link ):not( .not-tab )",
      initialTabHref,
      initialTabID,
      url;

    // Get the current tab
    if ("" !== window.location.hash && $(window.location.hash + "-tab.epfw-turn-into-tab").length > 0) {
      initialTabHref = window.location.hash;
    } else {
      initialTabHref = $(navTabSelector + ":first").attr("href");
    }

    initialTabID = initialTabHref + "-tab";
    if (initialTabID == "#license-tab" || initialTabID == "#support-tab") {
      $(".sabox-preview").hide();
    } else {
      $(".sabox-preview").show();
    }

    // Make the first tab active by default
    $(navTabSelector + '[href="' + initialTabHref + '"]').addClass("nav-tab-active");

    // Make all the tabs, except the first one hidden
    $(".epfw-turn-into-tab").each(function (index, value) {
      if ("#" + $(this).attr("id") !== initialTabID) {
        $(this).hide();
      }
    });

    $(navTabSelector).click(function (event) {
      var clickedTab = $(this).attr("href") + "-tab";

      if (clickedTab == "#license-tab" || clickedTab == "#support-tab") {
        $(".sabox-preview").hide();
      } else {
        $(".sabox-preview").show();
      }

      $(navTabSelector).removeClass("nav-tab-active"); // Remove class from previous selector
      $(this).addClass("nav-tab-active").blur(); // Add class to currently clicked selector

      $(".epfw-turn-into-tab").each(function (index, value) {
        if ("#" + $(this).attr("id") !== clickedTab) {
          $(this).hide();
        }

        $(clickedTab).fadeIn();
      });
    });
  } // adminTabSwitching

  $(document).on("click", function (e) {
    if ($(e.target).closest(".iris-picker").length === 0) {
      $(".iris-picker").hide();
    }
  });

  $(document).ready(function () {
    var elements = context.find(".saboxfield"),
      sliders = context.find(".sabox-slider"),
      colorpickers = context.find(".sabox-color");

    elements.each(function ($index, $element) {
      var element = $($element),
        value = getElementValue(element),
        elements = context.find(".show_if_" + element.attr("id"));
      if (value && "0" !== value) {
        elements.removeClass("hide");
      } else {
        elements.addClass("hide");
      }
    });
    if (sliders.length > 0) {
      sliders.each(function ($index, $slider) {
        var input = $($slider).parent().find(".saboxfield"),
          max = input.data("max"),
          min = input.data("min"),
          step = input.data("step"),
          value = parseInt(input.val(), 10);

        $($slider).slider({
          value: value,
          min: min,
          max: max,
          step: step,
          slide: function (event, ui) {
            input.val(ui.value + "px").trigger("change");
          },
        });
      });
    }
    if (colorpickers.length > 0) {
      colorpickers.each(function ($index, $colorpicker) {
        $($colorpicker).wpColorPicker({
          change: function (event, ui) {
            jQuery(event.target).val(ui.color.toString()).trigger("change");
          },
        });
      });
    }

    adminTabSwitching();

    $(".saboxplugin-tabs-wrapper").on("click", "li", function () {
      $(".saboxplugin-tabs-wrapper ul li").removeClass("active");
      $(".saboxplugin-tab").hide();
      $(this).addClass("active");
      $(".saboxplugin-tab-" + $(this).data("tab")).show();
    });

    window.onmessage = function (e) {
      if (e.data.messageType == "sabox-iframe") {
        document.querySelector("span.sabox-modal-selected-path").innerText = e.data.pathToSelect;
      }
      if (e.data.messageType == "sabox-close-iframe") {
        const pathToSelect = document.querySelector("span.sabox-modal-selected-path").innerText;
        const id = document.querySelector("div.sabox-modal").getAttribute("data-element-id");
        const element = document.querySelector("div.sabox-modal").getAttribute("data-element");
        document.querySelector("input#" + element).value = pathToSelect;
        document.querySelector("div.sabox-modal").style.display = "none";
        document.querySelector("body").style.overflow = "";
      }
    };

    $("button.sabox-modal-close-btn").on("click", function () {
      $("body").css("overflow", "");
      $("div.sabox-modal").css("display", "none");
    });

    $("#sabox_use_normal_click").on("change", function () {
      const iframe = $("iframe#sabox-modal-iframe")[0];
      iframe.contentWindow.postMessage(
        {
          messageType: "sabox-tick",
          canClick: this.checked,
        },
        "*"
      );
    });
  });

  // dismiss notice
  $(".sab-alert .notice-dismiss").on("click", function (e) {
    e.preventDefault();

    $(this).parents(".sab-alert").fadeOut();

    return false;
  });

  var preview_visible = window.localStorage.getItem("sab_preview");
  if (preview_visible !== "hidden") {
    preview_visible = "visible";
  }

  if (preview_visible === "visible") {
    $(".sabox-preview").show();
  }

  $('.sabox-table').on('change', 'select', function(e) {
    option_class = $('#' + $(this).attr('id') + ' :selected').attr('class');
    if(option_class == 'pro-option'){
        option_text = $('#' + $(this).attr('id') + ' :selected').text();
        $(this).val(0);
        open_upsell($(this).attr('id'));
        $('.show_if_' + $(this).attr('id')).hide();
    }
  });

  $(".sabox-content").on("click", ".confirm-action", function (e) {
    message = $(this).data("confirm");
    e.preventDefault();
    wfsab_swal
      .fire({
        type: "question",
        title: message,
        text: "",
        confirmButtonText: "Continue",
        cancelButtonText: "Cancel",
        showConfirmButton: true,
        showCancelButton: true,
      })
      .then((result) => {
        if (result.value) {
          window.location.href = $(this).attr("href");
        }
      });
  }); // confirm action before link click

  old_settings = $("#sabox-container *").not(".skip-save").serialize();
  $("#sabox-container").on("submit", function () {
    old_settings = $("#sabox-container *").not(".skip-save").serialize();
  });

  $(window).on("beforeunload", function (e) {
    if ($("#sabox-container *").not(".skip-save").serialize() != old_settings) {
      msg = "There are unsaved changes that will not be visible in the preview. Please save changes first.\nContinue?";
      e.returnValue = msg;
      return msg;
    }
  });

  $("#sab-preview").on("click", function (e) {
    if ($("#sabox-container *").not(".skip-save").serialize() != old_settings) {
      e.preventDefault();
      wfsab_swal
        .fire({
          type: "question",
          title: "There are unsaved changes that will not be visible in the preview. Please save changes first.<br />Continue?",
          text: "",
          confirmButtonText: "Continue",
          cancelButtonText: "Cancel",
          showConfirmButton: true,
          showCancelButton: true,
        })
        .then((result) => {
          if (result.value) {
            window.open($(this).attr("href"), "_blank");
          }
        });
    }

    return true;
  });

  function clean_feature(feature) {
    feature = feature || 'pricing-table';
    feature = feature.toLowerCase();
    feature = feature.replace(' ', '_');

    return feature;
  }

  $('#wpbody-content').on('click', '.open-upsell', function(e) {
    e.preventDefault();
    feature = $(this).data('feature');

    open_upsell(feature);

    return false;
  });

  function open_upsell(feature) {
    feature = clean_feature(feature);

    $('#sab-pro-dialog').dialog('open');

    $('#sab-pro-dialog .button-buy').each(function(ind, el) {
      tmp = $(el).data('href-org');
      tmp = tmp.replace('pricing-table', feature);
      $(el).attr('href', tmp);
    });
  } // open_upsell

  $('#sab-pro-dialog').dialog({
    dialogClass: 'wp-dialog sab-pro-dialog',
    modal: true,
    resizable: false,
    width: 800,
    height: 'auto',
    show: 'fade',
    hide: 'fade',
    close: function (event, ui) {
    },
    open: function (event, ui) {
      $(this).siblings().find('span.ui-dialog-title').html('WP Simple Author Box PRO is here!');
      sab_fix_dialog_close(event, ui);
    },
    autoOpen: false,
    closeOnEscape: true,
  });

  // show upsell popup every 3 months
  if (window.localStorage.getItem('sab_upsell_timestamp') === null ||
      (new Date().getTime() / 1000 - window.localStorage.getItem('sab_upsell_timestamp')) > (86400 * 90)) {
    window.localStorage.setItem('sab_upsell_timestamp', Math.round(new Date().getTime() / 1000));

    open_upsell('welcome');
  }

  if (location.hash == '#get-pro') {
    location.hash = '';
    open_upsell('sab-plugins-table');
  }

  $('.sab-saved').delay(10000).fadeOut(300);
})(jQuery);

function sab_fix_dialog_close(event, ui) {
  jQuery('.ui-widget-overlay').bind('click', function () {
    jQuery('#' + event.target.id).dialog('close');
  });
} // sab_fix_dialog_close
