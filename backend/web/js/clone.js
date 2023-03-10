$(function () {
    $('#btnAdd').click(function () {
        
        var num     = $('.clonedInput').length, // Checks to see how many "duplicatable" input fields we currently have
        
            newNum  = new Number(num + 1),      // The numeric ID of the new input field being added, increasing by 1 each time
            newElem = $('#entry' + num).clone().attr('id', 'entry' + newNum).fadeIn('slow'); // create the new element via clone(), and manipulate it's ID using newNum value
             // H2 - section
           newElem.find('.heading-main-reference').attr('id', 'ID' + newNum + '_main_reference').attr('name', 'ID' + newNum + '_main_reference').html('<b>Section #' + newNum + '</b>');
        //newElem.find('#pagemeta-description1').attr('id', 'ID' + newNum + '_pagemeta-description').val('');
        //$("#ID2_description").tinymce().remove();
        
        // Title - select
       
         //newElem.find('.description_it1').val'');
        //newElem.find('.file-caption-name').val(null);

    // Insert the new element after the last "duplicatable" input field
        $('#entry' + num).after(newElem);
        $('#entry' + newNum).find('input:text').val('');
        $('#entry' + newNum).find('textarea').val('');
        //alert('#entry' + newNum);
         //newElem.find('#pagemeta-title').val('');
        //$('#ID' + newNum + '_title').focus();

    // Enable the "remove" button. This only shows once you have a duplicated section.
        $('#btnDel').attr('disabled', false);

    // Right now you can only add 4 sections, for a total of 5. Change '5' below to the max number of sections you want to allow.
        //if (newNum == 5)
        //$('#btnAdd').attr('disabled', true).prop('value', "You've reached the limit"); // value here updates the text in the 'add' button when the limit is reached 
    });

    $('#btnDel').click(function () {
    // Confirmation dialog box. Works on all desktop browsers and iPhone.
        if (confirm("Are you sure you wish to remove this section? This cannot be undone."))
            {
                var num = $('.clonedInput').length;
                // how many "duplicatable" input fields we currently have
                $('#entry' + num).slideUp('slow', function () {$(this).remove();
                // if only one element remains, disable the "remove" button
                    if (num -1 === 1)
                $('#btnDel').attr('disabled', true);
                // enable the "add" button
                $('#btnAdd').attr('disabled', false).prop('value', "Add");});
            }
        return false; // Removes the last section you added
    });
    // Enable the "add" button
    $('#btnAdd').attr('disabled', false);
    // Disable the "remove" button
    $('#btnDel').attr('disabled', true);
});
 $(document).ready(function(){
$("#subscription-offer_status").change(function(){
  var subsc = $("#subscription-offer_status").val();
  if(subsc ==1)
  {
      $('.offerscl').show();
  } else {
      $('.offerscl').hide();
  }
});
});