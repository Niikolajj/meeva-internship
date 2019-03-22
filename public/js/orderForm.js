function addWeek(button)
{
    let div = button.closest(".lieferwoche");
    let parent = button.parentNode;
    let parentparent = parent.parentNode;
    parentparent.removeChild(parent);
    parentparent.insertAdjacentHTML("beforeend",document.getElementById('templateButton').value);
    let template = document.getElementById('templateBestellung');
    div.getElementsByClassName('lieferbestellungen')[0].innerHTML = template.value;
    let template2 = document.getElementById('templateLieferant');
    div.parentNode.insertAdjacentHTML("beforeend", template2.value);
    $('select').selectpicker();
}
function submitOrders(button)
{
    const woche = button.closest(".lieferwoche");
    let bestellungen = [];
    let data = {};
    console.log($(woche).find('form'));
    $(woche).find('form').each(function(){
        bestellungen.push($(this).serialize());
    });
    let lieferfield = woche.getElementsByTagName("input")[0];
    data['woche'] = document.getElementById("weekNr").value; //dataset.nr;
    console.log(lieferfield);
    data['lieferant'] = lieferfield.value;
    data['bestellungen'] = bestellungen;
    console.log(data);
    $.ajax({
        url: "/admin/order",
        type:'POST',
        data: data,
        success: function(response) {
            response = JSON.parse(response);
            switch(response['exit_code'])
            {
                case "added":
                    break;
                case "removed":
                    break;
                case "updated":
                    break;
                default:
                    break;
            }
        }
    })
}
function loadWeek(button)
{
    window.location.href = "/admin/"+document.getElementById("weekNr").value;
}