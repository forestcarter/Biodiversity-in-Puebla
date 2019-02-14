import React from "react";
//import BootstrapTable from 'react-bootstrap-table-next';

import SpeciesDisplay from "./SpeciesDisplay";

class Normaapp extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      speciesResultAve: [],
      speciesResultHierba: [],
      speciesResultArbol: [],
      speciesResultArbusto: [],
      speciesResultHerpetofauna: [],
      speciesResultMamifero: [],
    };
  }


  componentDidMount() {
    async function getSpecies(lifeform, idtype, idnumber) {
      let myapi = "https://biodiversidadpuebla.online/api/getspecies";
      if (window.location.host == "localhost:3000")
        myapi = "http://localhost:3000/api/getspecies";
      const rawResponse = await fetch(myapi, {
        method: "POST",
        headers: {
          Accept: "application/json",
          "Content-Type": "application/json;",
          mode: "cors"
        },
        body: JSON.stringify({
          lifeform: lifeform,
          idtype: idtype,
          idnumber: idnumber,
          useremail: document.getElementById("useremail").textContent
        })
      });
      let dataResult = await rawResponse.json();
      return dataResult;
    }
    
    const processArray=async(array) =>{
      for (const item of array) {
        getSpecies(item.toLowerCase(),'udp', idennum).then(myspeciesResult => {
          console.log(myspeciesResult)
          const newObject={}
          newObject['speciesResult'+item]=myspeciesResult
          this.setState(prevState => (
            newObject
          ));
        });
      }
    }
    


    processArray(['Ave','Arbol','Arbusto','Herpetofauna','Hierba','Mamifero'])
    
      // getSpecies('ave','udp', idennum).then(myspeciesResult => {
      //   this.setState(prevState => ({
      //     speciesResultAve: myspeciesResult
      //   }));
      // });
      // getSpecies('arbol','udp', idennum).then(myspeciesResult => {
      //   this.setState(prevState => ({
      //     speciesResultArbol: myspeciesResult
      //   }));
      // });
      // getSpecies('arbusto','udp', idennum).then(myspeciesResult => {
      //   this.setState(prevState => ({
      //     speciesResultArbusto: myspeciesResult
      //   }));
      // });
      // getSpecies('mamifero','udp', idennum).then(myspeciesResult => {
      //   this.setState(prevState => ({
      //     speciesResultMamifero: myspeciesResult
      //   }));
      // });
      // getSpecies('hierba','udp', idennum).then(myspeciesResult => {
      //   this.setState(prevState => ({
      //     speciesResultHierba: myspeciesResult
      //   }));
      // });
      // getSpecies('herpetofauna','udp', idennum).then(myspeciesResult => {
      //   this.setState(prevState => ({
      //     speciesResultHerpetofauna: myspeciesResult
      //   }));
      // });
  }

  render() {
    console.log(this.state)

    return (
      <div>
       
          <div className="speciesdisplay">
          <h4 className='normaTitles'>Ave</h4>
            <SpeciesDisplay speciesResult={this.state.speciesResultAve} lifeform='ave' />
          </div>
          <div className="speciesdisplay">
          <h4 className='normaTitles'>Arbol</h4>
            <SpeciesDisplay speciesResult={this.state.speciesResultArbol} lifeform='arbol' />
          </div>
          <div className="speciesdisplay">
          <h4 className='normaTitles'>Arbusto</h4>
            <SpeciesDisplay speciesResult={this.state.speciesResultArbusto} lifeform='arbusto' />
          </div>
          <div className="speciesdisplay">
          <h4 className='normaTitles'>Hierba</h4>
            <SpeciesDisplay speciesResult={this.state.speciesResultHierba} lifeform='hierba' />
          </div>
          <div className="speciesdisplay">
          <h4 className='normaTitles'>Mamifero</h4>
            <SpeciesDisplay speciesResult={this.state.speciesResultMamifero} lifeform='mamifero' />
          </div>
          <div className="speciesdisplay">
          <h4 className='normaTitles'>Herpetofauna</h4>
            <SpeciesDisplay speciesResult={this.state.speciesResultHerpetofauna} lifeform='herpetofauna' />
          </div>
          
        </div>

    
    );
  }
}

export default Normaapp;