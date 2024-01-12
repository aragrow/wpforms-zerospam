$ = jQuery;

$(document).ready(function () {
  

    $('#searchAdd').hide();
    /**
     * The code hides and shows the '#searchAdd' element based on the validity of the domain entered in the '#searchInput'. 
     * It also dynamically shows and hides checkboxes based on the entered search text.
     */
    $('#searchInput').on('input', function () {

      var searchText = $(this).val().toLowerCase().trim();
      console.log('Check class:');
      console.log($(this).attr('class'));
      $('#searchAdd').hide(); 

      if ($(this).hasClass('domains') && isValidDomain(searchText)) $('#searchAdd').show(); 
      else if ($(this).hasClass('emails') && isValidEmail(searchText)) $('#searchAdd').show();
      else if ($(this).hasClass('keywords')) $('#searchAdd').show();

      $('input[type="checkbox"]').each(function () {
        var label = $(this).parent().text().toLowerCase();

        if (label.indexOf(searchText) === -1) {
          $(this).parent().hide();
        } else {
          $(this).parent().show();
        }
      });
    });


    /**
     * This handler captures the value from an input with the ID 'searchInput', 
     *  creates a new checkbox element, and appends it to a list inside an element with the ID 'cbox'. 
     * Additionally, it hides the 'searchAdd' element and clears the value of 'searchInput'.
     */
    $('#searchAdd').on('click', function () {

      var searchText = $('#searchInput').val().toLowerCase();

      if ($(this).hasClass('domains')) searchText = addAtIfNeeded(searchText);

      $('input[type="checkbox"]').each(function () {
          $(this).parent().show();
      });

      // Create an li element
      var li = document.createElement('li');

      // Create a new checkbox element
      var checkbox = document.createElement('input');
      checkbox.type = 'checkbox';
      checkbox.name = 'wpfzs_block_domains[]';
      checkbox.value = searchText;
      checkbox.checked = true;
      checkbox.className = 'cbox-item';

      // Create a label for the checkbox
      var label = document.createElement('label');
      label.htmlFor = 'ckbox';
      label.className = 'new-item';
      label.appendChild(document.createTextNode(searchText));

      li.appendChild(checkbox);
      li.appendChild(label);

      console.log(li);

      $('#searchInput').val('');
      $(this).hide();

      
      var ul = $('#cbox li:eq(0)'); // Append to secon li

      console.log(ul.attr('id'));
      ul.after(li);
      
    });

    /**
     * Add a change event listener to the checkbox with the class "ckbox-item" within the div with the class "wpfzs".
     *  This code will provide a confirmation dialog for both checking and unchecking the checkbox, and it will only proceed 
     *  with the action if the user confirms. If the user clicks "Cancel," it will revert the checkbox to its previous state.
     */
    $('.cbox').on('change',' .cbox-item', function () {
      // Your event handling code here
      if ($(this).is(':checked')) {
        var isSure = confirm('Are you sure you want to check the checkbox?');
        if (!isSure) $(this).prop('checked', false);
      } else {
        var isSure = confirm('Are you sure you want to uncheck the checkbox?');
        if (!isSure) $(this).prop('checked', true);
      }
    });

    /**
     * This function uses a regular expression to check if the provided domain follows a simple format. 
     * It allows for domains that start with an optional '@' symbol, followed by alphanumeric characters, 
     * dots, and hyphens, ending with a period and at least two letters for the top-level domain (TLD).
     */
    function isValidDomain(input) {
      console.log('Checking Domain.');
      // Regular expression for a simple domain format
      var domainRegex = /^@?[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
  
      return domainRegex.test(input);
    }

    /**
     * This function uses a regular expression to check if the provided email follows a simple format. 
     */
    function isValidEmail(input) {
      console.log('Checking Email.');
      // Regular expression for a simple domain format
      var emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
  
      return emailRegex.test(input);
    }
    /**
     * This function will return the input string with '@' added to the beginning if it's not already present. 
     * If the '@' symbol is already in the input string, it will return the original input. It's a simple 
     * and effective way to ensure that the '@' symbol is included at the beginning of the string when needed. 
     */
    function addAtIfNeeded(input) {
        // Regular expression to check if '@' is present
        var atRegex = /@/;
    
        // Check if '@' is not present
        if (!atRegex.test(input)) {
            // Add '@' to the beginning of the string
            input = '@' + input;
        }
    
        return input;
    }

  });