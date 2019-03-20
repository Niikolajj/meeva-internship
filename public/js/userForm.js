function userSelection(selectpicker)
{
    form = $(selectpicker).closest("form");
    if(selectpicker.value=="new")
    {
        form[0].dataset.function = "add";
        form.find("i")[0].classList="fas fa-plus";
    }
    else
    {
        form[0].dataset.function ="delete";
        form.find("i")[0].classList="fas fa-trash-alt";
    }
    console.log(selectpicker.value);
}
function userEdit(input)
{
    var form = $(input).closest("form");
    if(form.find('#userSelect')[0].value=="new")
    {
        form[0].dataset.function = "add";
        form.find("i")[0].classList="fas fa-plus";
    }
    else
    {
        form[0].dataset.function ="update";
        form.find("i")[0].classList="fas fa-save";
    }

}
$('#userForm').submit(function(e) {
    //function proccUser(button) {
    e.preventDefault();
    e.stopPropagation();
    form = this;
    //var form = $(button).closest("form");
    console.log(form);
    var cmd = form.dataset.function;
    $.ajax({
        type: "POST",
        url: "admin/user/"+cmd,
        data: $(form).serialize(), // serializes the form's elements.
        success: function(data)
        {
            form.dataset.function ="delete";
            $(form).find("i")[0].classList="fas fa-trash-alt";
            alert(data); // show response from the php script.
        }
    });
});