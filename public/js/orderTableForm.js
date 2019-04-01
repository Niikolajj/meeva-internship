//ORDERING PROCESS
const buttonClasses = "btn-order btn py-0 ";
function processOrder(button)
{
    let data={};
    let tr = button.closest("tr");
    data["woche"]=$('#wochenNr').val();
    data["changed"]=tr.dataset.changed;
    data["id"]=tr.dataset.id;
    tr = $(tr);
    tr.find('input,select,textarea').each(function(){
        data[$(this).attr('name')]=$(this).val();
    });
    let order = button.dataset.function;
    //console.log(data);
    $.ajax({
        url: "/admin/"+order,
        type:'POST',
        data: data,
        success: function(data) {
            data = JSON.parse(data);

            const button = tr.find(".btn-order");
            switch(data['exit_code'])
            {
                case "added":
                    changeButton(button, "delete");
                    tr.attr("data-id", data['id']);
                    break;
                case "removed":
                    changeButton(button, "add");
                    tr.attr("data-id", "");
                    break;
                case "updated":
                    changeButton(button, "delete");
                    break;
                default:
                    changeButton(button, "");
                    break;
            }
        }
    })
}
//ORDERING BUTTON WATCH
function setChanged(obj)
{
    tr = $(obj).closest("tr");
    if(tr.data('id')!=="")
    {
        changeButton(tr.find(".btn-order"), "update");
        tr.attr('data-changed', 'true');
    }
    else
    {
        //console.log("");
    }
}

//NEW WEEK RELOAD
function loadWeek(event)
{
    const woche = document.getElementById("wochenNr").value;
    window.location.href="/admin/"+ woche;
}
function activeWeek(week)
{
    $('#inputTable').find('tr').each(function(){
        $(this).attr('data-id', '');
        $(this).find('.btn-order').each(function(){
            changeButton($(this), "add");
            $(this).attr("data-function", "add");
        })
    });
}
function changeButton(button, use)
{
    let useClass;
    let useIcon;
    let i = button.find("i");
    useClass = (use!=="add")? "btn-primary" :  "btn-outline-primary";
    button.ClassList = buttonClasses + useClass;
    button.attr('data-function', use);
    switch(use)
    {
        case "add":
            useIcon = "fas fa-plus";
            break;
        case "update":
            useIcon = "fas fa-save";
            break;
        case "delete":
            useIcon = "fas fa-trash-alt";
            break;
        default:
            useIcon = "fas fa-question";
            break;
    }
    i[0].className = useIcon;
}