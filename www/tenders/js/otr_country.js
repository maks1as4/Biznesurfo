function otr_get_selected (name1)
{
    var sel=[];
    if (!name1) name1="otr_root_content_0";
    var child1 = document.getElementById(name1).childNodes;
    for (i = 0; i < child1.length; i++)
    {
        child2 = child1[i].childNodes;
        for (j = 0; j < child2.length; j++)
        {
            child3 = child2[j].childNodes;
            for (k = 0; k < child3.length; k++)
            {
                if (child3[k].type == "checkbox")
                {
                    if (child3[k].checked) sel.push (child3[k].id.replace(/[^0-9]*(\d+).*/,"$1"));
                }
            }
        }
    }
    return (sel);
}

function otr_selected(name1, name2)
{
    var check=document.getElementById(name1).checked;
    var child1=document.getElementById(name2).childNodes;
    for (i = 0; i < child1.length; i++)
    {
        child2 = child1[i].childNodes;
        for (j = 0; j < child2.length; j++)
        {
            if (child2[j].type=="checkbox") child2[j].checked=check;

            child3 = child2[j].childNodes;
            for (k=0; k<child3.length; k++)
            {
                if (child3[k].type=="checkbox") child3[k].checked=check;
            }
        }
    }
}

function show_selected_otr (name1, name2, name3)
{	
    var re=/(.|\n|\r)*(minus|plus)+(.|\n|\r)*/g;
    var child1=document.getElementById(name1).childNodes;
    var otr_img,checkeds=0,start=0;
    var otr_root_checkbox=document.getElementById(name2).checked;
    var check_full_all=1;
    for (i=0; i < child1.length; i++) // child1.length=96
    {
        if ((child1[i].id+"").substr(0,3)=="otr")
        {
            start++;
            var child2=child1[i].childNodes;
            for (j=0; j<child2.length; j++)
            {
                if (child2[j].type=="checkbox") otr_checkbox=child2[j].checked;
                var child3 = child2[j].childNodes;
                for (k=0; k<child3.length; k++)
                {
                    if (child3[k].src) otr_img=child3[k].src;
                }
            }
        }
        check=0;
        check_full=1;
        if ((child1[i].id+"").substr(0,7)=="subotrs")
        {
            start++;
            var child2=child1[i].childNodes;
            for (j=0; j<child2.length; j++)
            {
                if (! child2[j].id) continue;
                var child3=child2[j].childNodes;
                for (k=0; k<child3.length; k++)
                {
                    if (child3[k].type=="checkbox")
                    {
                        if (child3[k].checked)
                        {
                            check=1;
                            checkeds=1;
                        }
                        else
                            check_full=0;
                    }
                }
            }
        }
        if (start==2)
        {
            if (! check_full) check_full_all=0;
            otr="otr_"+child1[i].id.replace(/[^0-9]*(\d+).*/,"$1");
            if (check && otr_img.replace(re,"$2")=="plus")
            {
                otr_on_off (otr, child1[i].id);
            }
            if (check==0 && otr_img.replace(re,"$2")=="minus")
            {
                otr_on_off (otr, child1[i].id);
            }
            if (check_full && ! otr_checkbox)
            {
                child4=document.getElementById(otr).childNodes;
                for (n=0; n<child4.length; n++)
                {
                    if (child4[n].type=="checkbox") child4[n].checked=1;
                }
            }
            if (! check && otr_checkbox)
            {
                child4=document.getElementById(otr).childNodes;
                for (n=0; n<child4.length; n++)
                {
                    if (child4[n].type=="checkbox") child4[n].checked=0;
                }
            }
            start=0;
        }
    }
    if (checkeds && document.getElementById(name3).innerHTML.replace(re,"$2")=="plus")
    {
        otr_on_off(name3,name1);
    }
    if (! checkeds && document.getElementById(name3).innerHTML.replace(re,"$2")=="minus")
    {
        otr_on_off(name3,name1);
    }
    if (check_full_all && ! otr_root_checkbox)
    {
        document.getElementById(name2).checked=1;
    }
    if (! check_full_all && otr_root_checkbox)
    {
        document.getElementById(name2).checked=0;
    }
}

function otr_on_off (name1, name2)
{
    var i, check;
    var childs = document.getElementById(name1).childNodes;
    for (i=0; i<childs.length; i++)
    {
        if (childs[i].type=="checkbox") check=childs[i].checked;
    }

    if (document.getElementById(name2).style.display=="none")
    {
        document.getElementById(name1).innerHTML = document.getElementById(name1).innerHTML.replace("plus","minus");
        document.getElementById(name2).style.display="block";
    }
    else
    {
        document.getElementById(name1).innerHTML = document.getElementById(name1).innerHTML.replace("minus","plus");
        document.getElementById(name2).style.display="none";
    }

    for (i=0; i<childs.length; i++)
    {
        if (childs[i].type=="checkbox") childs[i].checked=check;
    }
}

function country_get_selected (name1)
{
    var sel=[];
    if (! name1) name1="country_root_content_0";
    var child1=document.getElementById(name1).childNodes;
    for (i=0; i<child1.length; i++)
    {
        child2=child1[i].childNodes;
        for (j=0; j<child2.length; j++)
        {
            child3=child2[j].childNodes;
            for (k=0; k<child3.length; k++)
            {
                if (child3[k].type=="checkbox")
                    if (child3[k].checked)
                        if (child3[k].id.substr(0,6)=="region")
                            sel.push (child3[k].id.replace(/[^0-9]*(\d+).*/,"$1"));

                child4=child3[k].childNodes;
                for (l=0; l<child4.length; l++)
                    if (child4[l].checked)
                        if (child4[l].id.substr(0,6)=="region")
                            sel.push (child4[l].id.replace(/[^0-9]*(\d+).*/,"$1"));
            }
        }
    }
    return (sel);
}

function country_selected (name1, name2)
{
    var check=document.getElementById(name1).checked;
    var child1=document.getElementById(name2).childNodes;
    for (i=0; i<child1.length; i++)
    {
        child2=child1[i].childNodes;
        for (j=0; j<child2.length; j++)
        {
            if (child2[j].type=="checkbox") child2[j].checked=check;

            child3=child2[j].childNodes;
            for (k=0; k<child3.length; k++)
            {
                if (child3[k].type=="checkbox") child3[k].checked=check;

                child4=child3[k].childNodes;
                for (l=0; l<child4.length; l++)
                {
                    if (child4[l].type=="checkbox") child4[l].checked=check;
                }
            }
        }
    }
}

function show_selected_country (name1, name2)
{
    var re=/(.|\n|\r)*(minus|plus)+(.|\n|\r)*/g;
    var country_img,checkeds=0,start=0;
    var child1=document.getElementById(name1).childNodes;
    var save_prev_div;
    for (i=0; i<child1.length; i++)
    {
            var child2=child1[i].childNodes;
            for (j=0; j<child2.length; j++)
                    if ((child2[j].src+"").replace(re,"$2")=="plus" ||
                            (child2[j].src+"").replace(re,"$2")=="minus")
                                var level1_img=(child2[j].src+"").replace(re,"$2"); // root
            if (child1[i].type=="checkbox")
                    var level1_checkbox=child1[i];
    }

    var child1=document.getElementById(name2).childNodes;
    var level2_check=0, level2_full_check=1;
    var for_level2_check=0, for_level2_full_check=1;
    for (i=0; i<child1.length; i++)
    {
        var child2=child1[i].childNodes;

        if ((child1[i].id+"").substr(0,7)=="country")
                level2_prev_div=child1[i].id;

        var level3_check=0, level3_full_check=1, level3_write=0, level2_checkbox;
        var for_level3_check=0, for_level3_full_check=1;
        for (j=0; j<child2.length; j++)
        {                 
                if (child2[j].type=="checkbox")
                {
                            level2_checkbox=child2[j]; // country
                            if (level2_checkbox.checked)
                                    level2_check=1;
                            else
                                    level2_full_check=0;
                }
                var child3=child2[j].childNodes;

                if ((child2[j].id+"").substr(0,8)=="district") 
                        level3_prev_div=child2[j].id;

                var level4_check=0, level4_full_check=1, level4_write=0, level3_checkbox;
                for (k=0; k<child3.length; k++)
                {
                        if ((child3[k].src+"").replace(re,"$2")=="plus" ||
                                (child3[k].src+"").replace(re,"$2")=="minus")
                                    level2_img=(child3[k].src+"").replace(re,"$2"); // country
                        if (child3[k].type=="checkbox")
                        {
                                    level3_checkbox=child3[k];  
                                    if (level3_checkbox.checked)
                                            level3_check=1;
                                    else
                                            level3_full_check=0;
                                    level3_write=1;
                        }

                        var child4=child3[k].childNodes;
                        for (l=0; l<child4.length; l++)
                        {
                                if ((child4[l].src+"").replace(re,"$2")=="plus" ||
                                        (child4[l].src+"").replace(re,"$2")=="minus")
                                            level3_img=(child4[l].src+"").replace(re,"$2"); // district
                                if (child4[l].type=="checkbox")
                                {
                                            level4_checkbox=child4[l].checked; // regions_district
                                            if (level4_checkbox)
                                                    level4_check=1;
                                            else
                                                    level4_full_check=0;
                                            level4_write=1;
                                }
                        }
                }
                if (level4_write)
                {
                        for_level3_check+=level4_check;
                        for_level3_full_check*=level4_full_check;
                        if (level4_full_check && ! level3_checkbox.checked)
                                level3_checkbox.checked=1;
                        if (! level4_check && level3_checkbox.checked)
                                level3_checkbox.checked=0;
                        if (level4_check && level3_img=="plus")
                                country_on_off (level3_prev_div, child2[j].id);
                        if (! level4_check && level3_img=="minus")
                                country_on_off (level3_prev_div, child2[j].id);
                }
        }
        if (level3_write)
        {
                for_level3_check=(level3_check+for_level3_check>0)? 1: 0;
                for_level3_full_check*=level3_full_check;
                for_level2_check+=for_level3_check+level3_check;
                for_level2_full_check*=for_level3_full_check*level3_full_check;

                if (for_level3_full_check && ! level2_checkbox.checked)
                        level2_checkbox.checked=1;
                if (! for_level3_check && level2_checkbox.checked)
                        level2_checkbox.checked=0;
                if (for_level3_check && level2_img=="plus")
                        country_on_off (level2_prev_div, child1[i].id);
                if (! for_level3_check && level2_img=="minus")
                        country_on_off (level2_prev_div, child1[i].id);
        }
    }
    for_level2_check=(level2_check+for_level2_check)>0? 1: 0; 
    for_level2_full_check*=level2_full_check;
    if (for_level2_full_check && ! level1_checkbox.checked)
            level1_checkbox.checked=1;
    if (! for_level2_check && level1_checkbox.checked)
            level1_checkbox.checked=0;
    if (for_level2_check && level1_img=="plus")
            country_on_off (name1, name2);
    if (! for_level2_check && level1_img=="minus")
            country_on_off (name1, name2);
}

function country_on_off (name1, name2)
{
    var i, check;
    var childs = document.getElementById(name1).childNodes;
    for (i=0; i<childs.length; i++)
    {
        if (childs[i].type=="checkbox") check=childs[i].checked;
    }

    if (document.getElementById(name2).style.display=="none")
    {
        document.getElementById(name1).innerHTML = document.getElementById(name1).innerHTML.replace("plus","minus");
        document.getElementById(name2).style.display="block";
    }
    else
    {
        document.getElementById(name1).innerHTML = document.getElementById(name1).innerHTML.replace("minus","plus");
        document.getElementById(name2).style.display="none";
    }

    for (i=0; i<childs.length; i++)
    {
        if (childs[i].type=="checkbox") childs[i].checked=check;
    }
}
