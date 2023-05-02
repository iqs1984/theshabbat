<!-- start Simple Custom CSS and JS -->
<script type="text/javascript">
var hasSubmittedField = document.querySelector('input[name="has_submitted"]');

// Check whether the field exists and whether its value is "yes"
if (hasSubmittedField && hasSubmittedField.value === "yes") {
  // If the user has already submitted the form, hide the pop-up
  document.querySelector('#94fb919').style.display = 'none';
}

var hasSubmittedField = document.querySelector('input[name="has_submitted"]');

// Set the value of the field to "yes"
hasSubmittedField.value = "yes";</script>
<!-- end Simple Custom CSS and JS -->
