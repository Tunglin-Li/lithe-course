jQuery(document).ready(function ($) {
  const featuresList = $(".wpaa-features-list");
  const template = wp.template("feature-row");

  // Make features sortable
  featuresList.sortable({
    handle: ".handle",
    axis: "y",
    update: function () {
      renumberFeatures();
    },
  });

  // Add new feature
  $(".add-feature").on("click", function () {
    const index = $(".feature-row").length;
    featuresList.append(template({ index: index }));
  });

  // Remove feature
  featuresList.on("click", ".remove-feature", function () {
    $(this).closest(".feature-row").remove();
    renumberFeatures();
  });

  // Renumber features after sorting or removal
  function renumberFeatures() {
    $(".feature-row").each(function (index) {
      $(this)
        .find("select, input")
        .each(function () {
          const name = $(this).attr("name");
          $(this).attr("name", name.replace(/\[\d+\]/, "[" + index + "]"));
        });
    });
  }

  // Handle repeatable items (features, learnings, etc.)
  $(".wpaa-repeatable-items").each(function () {
    const container = $(this);
    const type = container.data("type");
    const itemsList = container.find(".items-list");
    const template = wp.template(type + "-row");

    // Make items sortable
    itemsList.sortable({
      handle: ".handle",
      axis: "y",
      update: function () {
        renumberItems(itemsList);
      },
    });

    // Add new item
    container.on("click", ".add-item", function () {
      const index = container.find(".item-row").length;
      itemsList.append(template({ index: index }));
    });

    // Remove item
    container.on("click", ".remove-item", function () {
      $(this).closest(".item-row").remove();
      renumberItems(itemsList);
    });
  });

  // Handle video URL validation
  $(".wpaa-video-field").each(function () {
    const container = $(this);
    const urlInput = container.find(".video-url");

    urlInput.on("input", function () {
      const url = $(this).val();

      // Remove previous validation messages
      container.find(".validation-message").remove();

      if (!url) {
        return;
      }

      // Check if URL matches any platform pattern
      let isValid = false;
      for (const [platform, config] of Object.entries(
        wpaaFeatures.videoPlatforms
      )) {
        if (config.pattern.test(url)) {
          isValid = true;
          break;
        }
      }

      if (!isValid) {
        urlInput.after(
          `<div class="validation-message" style="color: #dc3232; font-size: 12px; margin-top: 4px;">${wpaaFeatures.i18n.invalidVideoUrl}</div>`
        );
      }
    });
  });

  function renumberItems(itemsList) {
    itemsList.find(".item-row").each(function (index) {
      $(this)
        .find("select, input")
        .each(function () {
          const name = $(this).attr("name");
          $(this).attr("name", name.replace(/\[\d+\]/, "[" + index + "]"));
        });
    });
  }
});
