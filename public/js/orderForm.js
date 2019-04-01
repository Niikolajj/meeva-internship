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
    let i = button.getElementsByTagName("i");
    i.classList="fas fa-utensils fa-spin";
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
        url: Routing.generate('processOrder'),
        type:'POST',
        data: data,
        success: function(response) {
            response = JSON.parse(response);
            
            switch(response['exit_code'])
            {
                case "added":
                case "updated":
                i.classList= "fas fa-check";
                anime({
                    targets: i,
                    scale: [{value: 1}, {value:0.85}, {value: 1.4},{value:1}],
                    easing: 'easeInOutSine',
                    duration: 850,
                    complete: function(anim){
                        anime({
                            targets:i,
                            easing: 'easeInOutSine',
                            scale:[{value:0.5, delay:200}],
                            duration:150,
                            complete: function(anim){
                                i.classList= "fas fa-cloud-upload-alt";
                                anime({
                                    targets:i,
                                    easing: 'easeInOutSine',
                                    scale:[{value:1}],
                                    duration:150,
                                });
                            }
                        })
                       
                    }
                  });
                setTimeout(function(){
                    
                }, 1500);
                    break;
                case "removed":
                    break;
                default:
                i.classList= "fas fa-question";
                    break;
                
            }
        }
    });
}
function lockWeek(button)
{
    button = $(button);
    let i = button[0].getElementsByTagName("i");
    i.classList="fas fa-spinner fa-pulse";
    button.attr('data-original-title',"Wird verarbeitet...");
    button.tooltip('show');
    const woche = button.closest(".lieferwoche");
    //let bestellungen = [];
    let data = {};
    //console.log($(woche).find('form'));
    //$(woche).find('form').each(function(){
    //    bestellungen.push($(this).serialize());
    //});
    let lieferfield = woche[0].getElementsByTagName("input");
    //data['woche'] = document.getElementById("weekNr").value; //dataset.nr;
    let week = document.getElementById("weekNr").value;
    //console.log(lieferfield);
    data['lieferant'] = lieferfield.value;
    //data['bestellungen'] = bestellungen;
    //console.log(data);
    $.ajax({
        url: Routing.generate('lockW', {
            'week' : week
        }),
        type:'POST',
        data: data,
        success: function(response) {
            response = JSON.parse(response);
            switch(response['exit_code'])
            {
                case "locked":
                    i.classList= "fas fa-lock";
                    button.attr('data-original-title',"Woche geschlossen!");
                    break;
                case "unlocked":
                    i.classList= "fas fa-lock-open";
                    button.attr('data-original-title',"Woche geöffnet!");
                    break;
                case "updated":
                    break;
                default:
                    i.classList= "fas fa-question";
                    button.attr('data-original-title',"Bestellt!");
                    break;
            }
            button.tooltip('show');
            anime({
                targets:i,
                easing: 'easeInOutSine',
                scale:[{value:1}, {value:0.85}, {value:1.4}, {value:1}],
                duration:850,
            });
        }
    });
}
function loadWeek(button)
{
    window.location.href = Routing.generate("adminW", {
        week: document.getElementById("weekNr").value
    });
    //window.location.href = "/admin/"+document.getElementById("weekNr").value;
}