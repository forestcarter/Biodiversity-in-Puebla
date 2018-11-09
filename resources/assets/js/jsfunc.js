
function buildDropdowns(tableName, menu, jsTable="Form"){
    if (!(menu)){
        menu='measurement'
    }
    const myTBody = document.getElementById(menu+"TBody"+jsTable)

    const selectList = document.createElement("select");
    selectList.id = menu+tableName+jsTable;
    selectList.name = "select"+tableName;
    selectList.className='form-control';
    const newTR = document.createElement("tr");
    newTR.id="row"+tableName;

    const dataLabel = document.createElement("LABEL");
    dataLabel.setAttribute("for",menu+tableName+jsTable)
    const lowerCaseTitle=tableName.split("_").join(" ")
    dataLabel.textContent=lowerCaseTitle.charAt(0).toUpperCase() + lowerCaseTitle.slice(1);
    dataLabel.className="dropDownTitles";

    const dataSelect = document.createElement("td");
    dataSelect.appendChild(selectList);
    
    newTR.appendChild(dataLabel)
    newTR.appendChild(dataSelect);

    if (jsTable==="Form"){
        
        const myCols = tabletoColumns[tableName];
        const newColRows = createRows (tableName,menu,myCols, 0)
        const spacer1 = document.createElement("td");
        spacer1.innerHTML="&nbsp;";
        const spacer2 = document.createElement("td");
        spacer2.innerHTML="&nbsp;";
        const spacer3 = document.createElement("td");
        spacer3.innerHTML="&nbsp;";
        const spacer4 = document.createElement("td");
        spacer4.innerHTML="&nbsp;";
        newColRows[0].prepend(spacer1)
        newColRows[0].prepend(spacer2)
        newColRows[1].prepend(spacer3)
        newColRows[1].prepend(spacer4)

        const spacerTR1 =document.createElement("tr");
        spacerTR1.className="myspacer";
        for(let i=0;i<9;i++){
            spacerTR1.appendChild(spacer1.cloneNode(true));
        }
        const spacerTR2 =document.createElement("tr");
        spacerTR2.appendChild(spacer1.cloneNode(true));

        newColRows[1].id=tableName+"inputrow"
        newColRows[0].id=tableName+"columnsrow"
        newColRows[1].className=newColRows[1].className+" hiddenrows"
        newColRows[0].className=newColRows[0].className+" hiddenrows"

        myTBody.prepend(spacerTR2);
        myTBody.prepend(spacerTR1);
        myTBody.prepend(newColRows[1]);
        myTBody.prepend(newColRows[0]);
        myTBody.prepend(newTR);
    }else{
        const spacerTR =document.createElement("tr");
        spacerTR.id="spacer"+tableName;
        const spacer1 = document.createElement("td");
        spacer1.innerHTML="&nbsp;";
        spacerTR.appendChild(spacer1);
        myTBody.prepend(spacerTR);
        myTBody.prepend(newTR);
    }
}

//////////////////////////////////////////////////////////////////////////////////////                
 
function selectOptionsCreate(tableName, menu, preApproved=true, jsTable="Form",approvedList=[], withRows=false, withNuevo=true) {
    let myId=(withRows? "row"+numRows+tableName+"Form" : menu+tableName+jsTable )
    if (tableName==="observaciones") myId="measurementobservacionesObservaciones";
    if (tableName==="medicion") myId="measurementmedicionMedicion";
    if (tableName==="linea_mtp") myId="measurementlinea_mtpSelect";

    if (!!(document.getElementById(myId))){   
        ("row"+numRows+tableName+"Form");
        const mySelection = document.getElementById(myId);
        if (tableName.split('_')[0]==="observacion") tableName = tableName.replace("observacion", "especie");
        
        let mycurrentlist=completetitlevallist[tableName];
        mycurrentlist= tableName==="observaciones"? ['ave','arbol','arbusto','mamifero','herpetofauna','hierba']:mycurrentlist

        let frag = document.createDocumentFragment(),elOption;
        elOption = frag.appendChild(document.createElement('option'));
            elOption.value = "notselected";
            elOption.innerHTML =" ";
        if (withNuevo){
            elOption = frag.appendChild(document.createElement('option'));
            elOption.value = "Nuevo";
            elOption.innerHTML ="Nuevo";
        }

        if (preApproved===false){
            for (let i = 0; i<approvedList[tableName].length; i++){
                elOption = frag.appendChild(document.createElement('option'));
                elOption.value = mycurrentlist[approvedList[tableName][i]];
                elOption.innerHTML = mycurrentlist[approvedList[tableName][i]];
            }
        }else{
            for (let i = 0; i<mycurrentlist.length; i++){
                elOption = frag.appendChild(document.createElement('option'));
                elOption.value = mycurrentlist[i];
                elOption.innerHTML =mycurrentlist[i];
            }
        }
        while (mySelection.hasChildNodes()) {
            mySelection.removeChild(mySelection.lastChild);
        }
          mySelection.appendChild(frag);
    }
}

//////////////////////////////////////////////////////////////////////////////////////        

function clearForm(menu, jsTable){
    if (!(menu)){
        menu='measurement'
    }
    const myTBody = document.getElementById(menu+"TBody"+jsTable);
    if (!!(myTBody)){
        while (myTBody.hasChildNodes()) {
            myTBody.removeChild(myTBody.lastChild);
        }
    }
}

//////////////////////////////////////////////////////////////////////////////////////      

function addOnChangeMTP(tableName, menu){
                    const currentFunction= function(tableName,menu){
                        const myChoice = document.getElementById(menu+tableName+"Select").value;
                        clearForm(menu,"Form")
                        if (myChoice=="Nuevo"){
                            clearForm(menu, "Medicion")
                            clearForm(menu, "Observaciones")
                            clearForm(menu, "Numero")
                            clearForm(menu, "Form")
                            buildForm("linea_mtp", menu, "Coordenadas de Linea")
                                const newMTPdropdowns=[ "predio", "municipio","estado",]
                                newMTPdropdowns.forEach(function(newTable){
                                    buildDropdowns(newTable,menu,"Form");
                                    selectOptionsCreate(newTable,menu);
                                    addOnChangeFKey(newTable, menu) 
                                })
                        }else{
                        //This is when an old linea_mtp is selected
                            clearForm(menu,"Observaciones")
                            clearForm(menu,"Medicion")
                            buildDropdowns("medicion",menu, "Medicion");
                            selectOptionsCreate("medicion",menu);
                            addOnChangeMedicion('medicion', menu) 
                    
                    }
                }
                const currentOnChange =function() {currentFunction(tableName,menu)  }

                const getSelection = document.getElementById(menu+tableName+"Select");
            getSelection.onchange=currentOnChange;
               }
               

//////////////////////////////////////////////////////////////////////////////////////          

function addOnChangeFKey(tableName, menu){
    const getSelection = document.getElementById(menu+tableName+"Form");
    const currentFunction3 = function(tableName, menu){
        const myChoice = getSelection.value;
        const inputRow = document.getElementById(tableName+"inputrow");
        const colRow = document.getElementById(tableName+"columnsrow");
            if (myChoice==="Nuevo"){
                colRow.classList.remove("hiddenrows");
                inputRow.classList.remove("hiddenrows");
            
            }else{  
                colRow.classList.add("hiddenrows");
                inputRow.classList.add("hiddenrows");

            }
    }  
    const currentOnChange3 =function() {currentFunction3(tableName,menu)}   
             getSelection.onchange=currentOnChange3;
            }
            
//////////////////////////////////////////////////////////////////////////////////////       
function addOnChangeMedicion(tableName, menu){
    const getSelection = document.getElementById(menu+tableName+"Medicion");
    const currentFunctionMedicion = function(tableName, menu){
        const myChoice = document.getElementById(menu+tableName+"Medicion").value;
            if (myChoice==="Nuevo"){
                clearForm(menu,"Observaciones")
                clearForm(menu,"Numero")
                clearForm(menu,"Form")
                buildForm("medicion",menu);
                buildForm("personas",menu, "Brigada");
                buildForm("gps",menu, "GPS",true);
                buildForm("camara",menu, "Camara",true);
               
            }else{  
                clearForm(menu,"Observaciones")
                clearForm(menu,"Numero")
                clearForm(menu,"Form")
                buildDropdowns("observaciones",menu, "Observaciones");
                selectOptionsCreate("observaciones", menu, true, "Form",[], false, false);
                addOnChangeObservaciones(menu)
                                           
            }
    }  
    const currentOnChangeMedicion =function() {currentFunctionMedicion(tableName,menu)}   
             getSelection.onchange=currentOnChangeMedicion;
            }
            
//////////////////////////////////////////////////////////////////////////////////////         

function createRows (tableName,menu,myCols, myNumRow, obs=false,customList=[]){
    
    const speciesTable =[];
    const columnRowOld = document.createElement("tr");
    const firstDataRow = document.createElement("tr");
    firstDataRow.class="dataRows";

    myCols.sort(function(a, b){
            if(a < b) return -1;
            if(a > b) return 1;
            return 0;
                    })
    myCols.sort(function(a, b){
        if(a=='notas') return 1;
        if(b=='notas') return -1;
        if(a.indexOf('omienzo')!==-1) return -1;
        if(b.indexOf('omienzo')!==-1)return 1;
        if(a.indexOf('long')!==-1) return -1;
        if(b.indexOf('long')!==-1)return 1;
        if(a.indexOf('lat')!==-1) return -1;
        if(b.indexOf('lat')!==-1)return 1;
        if(a.indexOf('hora')!==-1) return -1;
        if(b.indexOf('hora')!==-1)return 1;
        if(a.indexOf('fecha')!==-1) return -1;
        if(b.indexOf('fecha')!==-1)return 1;
        if(a.length==1) return 1;
        if(b.length==1) return -1;
        if(a.length==2) return 1;
        if(b.length==2) return -1;
        return 0;
                })
    


    if (customList.length>=1) myCols=customList

    if(obs){
        if (tableName=='observacion_arbol'|| tableName=='observacion_arbusto'){
            const cuadranteLabel = document.createElement("td");
            cuadranteLabel.textContent="Cuadrante";
            cuadranteLabel.className="formcolumnlabels"
            const cuadranteBox = document.createElement("td");
            const cuadranteInput = document.createElement("INPUT");
            cuadranteInput.setAttribute("type", "text");
            cuadranteInput.name = "row"+myNumRow+'*'+tableName+'*cuadrante';
            cuadranteInput.id=`row${myNumRow}cuadrante`;
            cuadranteInput.value=1;
            cuadranteInput.className="cuadranteinput";
            cuadranteBox.appendChild(cuadranteInput);
            cuadranteBox.className="cuadrante";
            columnRowOld.appendChild(cuadranteLabel);
            firstDataRow.appendChild(cuadranteBox);
        }
        const speciesTable = tableName.replace("observacion", "especie");
        //Species drop Label
        const speciesLabelDrop = document.createElement("td");
        speciesLabelDrop.innerText=speciesTable;
        speciesLabelDrop.className="formcolumnlabels"
        columnRowOld.appendChild(speciesLabelDrop);
        //Species drop 
        const speciesInput = document.createElement("SELECT");
        speciesInput.id = "row"+myNumRow+tableName+"Form";//this needs to
        speciesInput.setAttribute("class",speciesTable);
        speciesInput.classList.add('allinputs');
        speciesInput.classList.add('form-control');


        speciesInput.name = "row"+myNumRow+"*"+tableName+ "*"+"species";
        const inputBox = document.createElement("td");
        inputBox.appendChild(speciesInput);
        firstDataRow.appendChild(inputBox);
        //Species comun Label
        const speciesLabelComun = document.createElement("td");
        speciesLabelComun.textContent="Nuevo Nombre Comun";
        speciesLabelComun.className="formcolumnlabels"
        columnRowOld.appendChild(speciesLabelComun);
         //Species cien Label
        const speciesLabelCien = document.createElement("td");
        speciesLabelCien.textContent="Nuevo Nombre Cientifico";
        speciesLabelCien.className="formcolumnlabels"
        columnRowOld.appendChild(speciesLabelCien);
        //Species comun inputbox
        const speciesBoxComun = document.createElement("INPUT");
        speciesBoxComun.setAttribute("type", "text");
        speciesBoxComun.classList.add("row"+myNumRow+"disableme")
        speciesBoxComun.classList.add('allinputs');
        speciesBoxComun.classList.add('form-control');

        speciesBoxComun.disabled=true;
        speciesBoxComun.name = "row"+myNumRow+"*"+tableName+ "*"+"comun";
        const boxContainerComun = document.createElement("td");
        boxContainerComun.appendChild(speciesBoxComun)
        firstDataRow.appendChild(boxContainerComun);
        //Species cien inputbox
        const speciesBoxCien = document.createElement("INPUT");
        speciesBoxCien.setAttribute("type", "text");
       
        speciesBoxCien.classList.add("row"+myNumRow+"disableme")
        speciesBoxCien.classList.add('allinputs');
        speciesBoxCien.classList.add('form-control');

        speciesBoxCien.disabled=true;
        speciesBoxCien.name = "row"+myNumRow+"*"+tableName+ "*"+"cientifico";
        const boxContainerCien = document.createElement("td");
        boxContainerCien.appendChild(speciesBoxCien)
        firstDataRow.appendChild(boxContainerCien);
    }
    myCols.forEach(function(val){
        let found = false;
        if (typeof(allPhp2[tableName]["fKeyCol"])!=="undefined"){
            found = !!allPhp2[tableName]["fKeyCol"].find(function(element) {
                return element==val;
            });
        }
            if (!(val.includes("iden")) && !found){
                const nameBox = document.createElement("td");
                const spacedval=val.split("_").join(" ");
                nameBox.innerText=spacedval.charAt(0).toUpperCase() + spacedval.slice(1);
                nameBox.className="formcolumnlabels"
                
                columnRowOld.appendChild(nameBox);
                columnRowOld.className=tableName+"columnrow"
                const textInput = document.createElement("INPUT");
                if (val.substring(0, 5).toLowerCase()=="fecha"){
                    textInput.classList.add('fechainputs');

                    textInput.setAttribute("type", "date");
                }else if (val.substring(0, 4).toLowerCase()==="hora"){
                    textInput.classList.add('horainputs');
                    textInput.setAttribute("type", "time");
                }else{
                    textInput.setAttribute("type", "text");
                }
                textInput.id = tableName+val;
                textInput.classList.add(tableName+val);
                if(obs){
                    textInput.classList.add("row"+myNumRow+"*"+tableName);
                }
                textInput.classList.add('allinputs');
                textInput.classList.add('form-control');

                textInput.name = ("row"+myNumRow+"*"+tableName+ "*"+val).toLowerCase();
                const inputBox = document.createElement("td");
                inputBox.appendChild(textInput);
                firstDataRow.className=tableName+"inputrow";
                if (val!=='Foto'){
                    firstDataRow.appendChild(inputBox);
                }
            }
            
    })
    if(obs){
        const fotoInput = document.createElement("INPUT");
        fotoInput.setAttribute("type", "file");
        fotoInput.name = ("row"+myNumRow+"*"+tableName+ "*"+'foto').toLowerCase();
        fotoInput.id = tableName+'foto';
        const fotoInputBox = document.createElement("td");
        fotoInputBox.appendChild(fotoInput);

        firstDataRow.appendChild(fotoInputBox);    
        return [columnRowOld, firstDataRow,speciesTable]
    }
   
    
    return [columnRowOld, firstDataRow,speciesTable]
}

//////////////////////////////////////////////////////////////////////////////////////          


function buildForm(tableName, menu, myTitle, spacers=false, obs=false, customList=[], buttons=true){
    if (!(menu)){
        menu='measurement'
    }
    const myTBody = document.getElementById(menu+"TBodyForm");
    let myCols = tabletoColumns[tableName];
    const spaceRow = document.createElement("tr");
    spaceRow.className="formtitles"
    spaceRow.innerHTML="<br> ";
    if (myTitle!=="none") spaceRow.innerHTML=myTitle;
    const buttonRow = document.createElement("tr");
    let mySubmit =  document.createElement("INPUT");
    mySubmit.setAttribute("type", "submit");
    mySubmit.id= menu+tableName+"Submit";
    mySubmit.className= "mySubmit";
    if(document.getElementsByClassName("mySubmit").length>0) mySubmit= document.getElementsByClassName("mySubmit")[0];
    var newRows = createRows(tableName,menu,myCols,0,obs,customList)

    const addElementRow = document.createElement("BUTTON");
    addElementRow.setAttribute("type", "button");
    addElementRow.id = "addElementRow"+tableName;
    //addElementRow.className = "addElementRow";
    const subtractElementRow = document.createElement("BUTTON");
    subtractElementRow.setAttribute("type", "button");

    subtractElementRow.id = "subtractElementRow";
    addElementRow.innerText="+"
    subtractElementRow.innerText="-"
    
    addElementRow.onclick=function(){return addRow(myTBody,tableName, myCols, obs,customList) }; 
    subtractElementRow.onclick=function(){return subtractRow(myTBody,tableName) }; 
    const buttonBox = document.createElement("td");
    buttonBox.appendChild(addElementRow);
    buttonBox.appendChild(subtractElementRow);
    buttonRow.appendChild(buttonBox);

    const spacer1 = document.createElement("td");
    spacer1.innerHTML="&nbsp;";

    const spacerTR1 =document.createElement("tr");
    spacerTR1.className="myspacer";
    for(let i=0;i<newRows[0].childElementCount;i++){
        spacerTR1.appendChild(spacer1.cloneNode(true));
    }
    const spacerTR2 =document.createElement("tr");
    spacerTR2.appendChild(spacer1.cloneNode(true));

    const bottomSpacer =document.createElement("tr");
    bottomSpacer.id=tableName+"bottomspacer"
    bottomSpacer.appendChild(spacer1.cloneNode(true));

    if (tableName=='medicion' || !buttons){
        myTBody.appendChild(mySubmit);
        myTBody.insertBefore(newRows[0], mySubmit);
        myTBody.insertBefore(newRows[1], mySubmit);
        //myTBody.insertBefore(bottomSpacer, mySubmit);
    }else{

        if (spacers){

                myTBody.appendChild(mySubmit);
            myTBody.insertBefore(spacerTR1, mySubmit);
            myTBody.insertBefore(spacerTR2, mySubmit);
            myTBody.insertBefore(spaceRow, mySubmit);
            myTBody.insertBefore(buttonRow, mySubmit);
            myTBody.insertBefore(newRows[0], mySubmit);
            myTBody.insertBefore(newRows[1], mySubmit);
            myTBody.insertBefore(bottomSpacer, mySubmit);

        }else{

            myTBody.appendChild(mySubmit);
            myTBody.insertBefore(spaceRow, mySubmit);
            myTBody.insertBefore(buttonRow, mySubmit);
            myTBody.insertBefore(newRows[0], mySubmit);
            myTBody.insertBefore(newRows[1], mySubmit);
            myTBody.insertBefore(bottomSpacer, mySubmit);
        }
    }
    
    if (obs){
        selectOptionsCreate(tableName, menu,true, "Form",[],true);

        selectSpeciesOnChange(tableName, menu, 0);
        
        //selectOptionsCreate(tableName, menu, true, "Form",[], 0)

    }
}


//////////////////////////////////////////////////////////////////////////////////////          

function addRow(myTBody,tableName, myCols,obs,customList=[]){
    var menu='selection';
    const bottomSpacer = document.getElementById(tableName+"bottomspacer");
    numRows++;
    var newRows = createRows(tableName,menu,myCols,numRows,obs,customList )
    newRows[1].class="addedRow";
    newRows[1].id="addedRow"
    myTBody.insertBefore(newRows[1], bottomSpacer);
    if(obs){
        selectSpeciesOnChange(tableName, menu, numRows);
        selectOptionsCreate(tableName, menu, true, "Form",[], true)

    }
 }
//////////////////////////////////////////////////////////////////////////////////////          

function subtractRow(myTBody,tableName){
    myTBody.childNodes.forEach(function(val,index){
        if (val.id==tableName+"bottomspacer"){
            let targetNode= myTBody.childNodes[index-1]
            if (targetNode.id=="addedRow") {
                myTBody.removeChild(targetNode)
                numRows--;
            }
            

            
        }
    })    
}

//////////////////////////////////////////////////////////////////////////////////////          

function addOnChangeObservaciones(menu){
    const getSelection = document.getElementById("measurementobservacionesObservaciones");
    const currentFunction3 = function(tableName, menu){
        const myChoice = 'observacion_'+document.getElementById("measurementobservacionesObservaciones").value;
        numRows=0;
        clearForm(menu,"Numero")
        clearForm(menu,"Form")
        if (myChoice!=='notselected'){
            let numberPoints = 4;
            let transpunto = 'Transecto';
            if (myChoice=="observacion_ave" || myChoice=="observacion_mamifero" ){
                transpunto = 'Punto';
                numberPoints = 5;
            }
            if (myChoice=="observacion_arbol" || myChoice=="observacion_arbusto" ){
                buildDropdowns("Punto",menu, "Numero");
                const mySelectionPunto = document.getElementById(`measurementPuntoNumero`);
                //Add Number Options
                let fragPunto = document.createDocumentFragment(),elOption;
                elOption = fragPunto.appendChild(document.createElement('option'));
                elOption.value = "notselected";
                elOption.innerHTML =" ";
                for (let i = 1; i<=8; i++){
                    elOption = fragPunto.appendChild(document.createElement('option'));
                    elOption.value =i;
                    elOption.innerHTML =i;
                }
                mySelectionPunto.appendChild(fragPunto);
            }
            buildDropdowns(transpunto,menu, "Numero");
            const mySelection = document.getElementById('measurement'+transpunto+'Numero');
            //Add Number Options
            
            let frag = document.createDocumentFragment(),elOption;
            elOption = frag.appendChild(document.createElement('option'));
                elOption.value = "notselected";
                elOption.innerHTML =" ";
            for (let i = 1; i<=numberPoints; i++){
                elOption = frag.appendChild(document.createElement('option'));
                elOption.value =i;
                elOption.innerHTML =i;
            }
            while (mySelection.hasChildNodes()) {
                mySelection.removeChild(mySelection.lastChild);
            }
            mySelection.appendChild(frag);
            clearForm(menu,"Form")
            
            buildCustomForm(myChoice,menu)
    }
    }
    const currentOnChange3 =function(tableName,menu) {currentFunction3(tableName,menu)}   
             getSelection.onchange=currentOnChange3;
}

//////////////////////////////////////////////////////////////////////////////////////          

function selectSpeciesOnChange(tableName, menu, numRows){
    const currentFunction2= function(tableName,numRows){
        const myChoice = document.getElementById("row"+numRows+tableName+"Form").value;
        const allMyRows= document.getElementsByClassName("row"+numRows+"*"+tableName)
        const colRow = document.getElementsByClassName("row"+numRows+"disableme");
        if (myChoice==="Nuevo"){
            colRow[0].disabled= false  
            colRow[1].disabled= false
        }else{
            colRow[0].disabled= true 
            colRow[1].disabled= true 
            colRow[0].value=""
            colRow[1].value=""     
        }
        if (myChoice==="0000"){
            for(let  i=0;i<allMyRows.length;i++){
                if (allMyRows[i].name.includes("hora")){
                    allMyRows[i].value="00:01"
                }
                else if(allMyRows[i].name.includes("fecha")){
                    allMyRows[i].value="1000-01-01"
                    
                }else{
                    allMyRows[i].value="0000"
                }
            }  
        }
        if (myChoice==="000"){
            for(let  i=0;i<allMyRows.length;i++){
                allMyRows[i].value="000"
            }  
        }




    }
    const currentOnChange2 =function() {currentFunction2(tableName,numRows)  }
    const getSelection = document.getElementById("row"+numRows+tableName+"Form");
    getSelection.onchange=currentOnChange2;
}



function buildCustomForm(obName,menu){
    let transPunto='punto';
    if(obName=='observacion_hierba'||obName=='observacion_herpetofauna'){
        transPunto='transecto';
    }
    let obsNameContext=(transPunto+"_" +obName.split('_')[1]);

    buildForm(obsNameContext, menu, ' ', false, false, [], false)
    
    buildForm(obName, menu, ' ', true, true, [])

    if (obName=='observacion_arbol'||obName=='observacion_arbusto'){
        const getSelectionAdd = document.getElementById(`addElementRow${obName}`)
        const getSelectionSubtract = document.getElementById('subtractElementRow')
        let getCuadrante0 = document.getElementById(`row${0}cuadrante`)
        getCuadrante0.setAttribute("readonly", true);
        for(let i=0;i<7;i++) {
            getSelectionAdd.onclick()
            let getCuadrante = document.getElementById(`row${i+1}cuadrante`)
            getCuadrante.value=Math.floor(i/2+1.5)
            getCuadrante.setAttribute("readonly", true);
        }
        numRows=0
        getSelectionAdd.disabled=true;
        getSelectionSubtract.disabled=true;
    }

}

function addOnChangeAdminTable(){
    const getSelection = document.getElementById('table_option');
    const currentFunction3 = function(tableName, menu){

        const myChoice = getSelection.value;
        const mySelection = document.getElementById('field_option')
        const mycurrentlist= tabletoColumns[myChoice]
        let frag = document.createDocumentFragment(),elOption;

        for (let i = 0; i<mycurrentlist.length; i++){
            if(!mycurrentlist[i].includes("iden")){
                elOption = frag.appendChild(document.createElement('option'));
                elOption.value = mycurrentlist[i];
                elOption.innerHTML =mycurrentlist[i];
            }
        }
    while (mySelection.hasChildNodes()) {
        mySelection.removeChild(mySelection.lastChild);
    }
    mySelection.appendChild(frag);



    }  
    const currentOnChange3 =function() {currentFunction3()}   
             getSelection.onchange=currentOnChange3;
            }
            



var numRows=0;
if(window.location.href.substr(-5)==='admin'){
    buildDropdowns( "usuario", "measurement", "Select" );
    selectOptionsCreate( "usuario",  "measurement",  true,  "Select", [],  false,  false);
    buildDropdowns( "usuario_permitido", "measurement", "Medicion" );
    selectOptionsCreate( "usuario_permitido",  "measurement",  true,  "Medicion", [],  false, false);
    addOnChangeAdminTable()

 
}else{
    buildDropdowns( "linea_mtp", "measurement", "Select" );
    selectOptionsCreate( "linea_mtp", "measurement", true,  "Select" );
    addOnChangeMTP( "linea_mtp",  "measurement");
}