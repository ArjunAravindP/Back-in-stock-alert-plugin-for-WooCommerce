jQuery(document).ready(function ($) {
  // When the "Notify Me" button is clicked
  $("#bis-notify-btn").on("click", function () {
    // Toggle the visibility of the subscription form
    $("#bis-subscribe-form").slideToggle()
  })

  // Handle form submission (optional)
  $("#bis-submit").on("click", function (e) {
    e.preventDefault()

    const email = $("#bis-email").val()
    const productId = $("#bis-notify-container").data("product-id")

    console.log("Product ID:", productId)

    if (!email) {
      alert("Please enter a valid email address.")
      return
    }

    // AJAX call to handle subscription
    $.ajax({
      url: bis_ajax.ajax_url, // Use the localized AJAX URL
      type: "POST",
      data: {
        action: "bis_subscribe", // The AJAX action defined in the PHP handler
        email: email,
        product_id: productId
      },
      success: function (response) {
        if (response.success) {
          alert("You have successfully subscribed!")
          $("#bis-subscribe-form").slideUp() // Hide the form after successful subscription
        } else {
          alert(response.data.message || "An error occurred. Please try again.")
        }
      },
      error: function () {
        alert("An error occurred. Please try again.")
      }
    })
  })
})
