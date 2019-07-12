import React from "react";
import Map from "./Map";
import MapControl from "./MapControl";
import FeatureInfoDisplay from "./FeatureInfoDisplay";
import fetchData from "../fetchData";

class Mapapp extends React.Component {
    constructor(props) {
        super(props);
        this.handleMapClick = this.handleMapClick.bind(this);
        this.handleSpeciesChange = this.handleSpeciesChange.bind(this);
        this.handleTotalDistinctChange = this.handleTotalDistinctChange.bind(this);
        this.handleOpacityChange = this.handleOpacityChange.bind(this);
        this.handleMaxChange = this.handleMaxChange.bind(this);
        this.handleFeatureClick = this.handleFeatureClick.bind(this);
		this.setDefaultMax = this.setDefaultMax.bind(this);
		this.handleOverlayChange = this.handleOverlayChange.bind(this);
		this.setRasterValue = this.setRasterValue.bind(this);

        this.state = {
			currentUdpId: -1,
			currentLineaId:-1,
			rasterOn : false,
            speciesResult: [],
            previous: 0,
            udp: 0,
			udpButton: false,
			lineaButton: false,
            clickLocation: { lat: 999.9, lng: 999.9 },
            mapSettings: {
                distinctOrTotal: "total_observaciones",
                myObsType: "ave",
                fillOpacity: 0.6,
                maxValue: 99
            },
            featureInfo: {
                properties: { message: "click somewhere", displayName: " " }
            }
        };
	}
	
	handleOverlayChange(name,type){
		this.setState((prevState) => ({
			rasterOn: (name=='Escenario85_2099_Temp_UNIATMOS_2015' && type=='overlayadd')
			?true
			:(name=='Escenario85_2099_Temp_UNIATMOS_2015' && type=='overlayremove')
				?false
				:prevState.rasterOn
        }));
	}

    getOutline(properties, category) {
        let email = useremail;
        let emailArray = [
            properties.ave_email,
            properties.arbol_email,
            properties.arbusto_email,
            properties.hierba_email,
            properties.herpetofauna_email,
            properties.mamifero_email
		];
        if (category == "color") {
            return emailArray.includes(email)
                ? "purple"
                : emailArray.some(el => el !== null)
					? "red"
					: "black";
        } else {
            return emailArray.includes(email)
                ? 3
                : emailArray.some(el => el !== null)
					? 2
					: 0.3;
        }
	}
	
	setRasterValue(value){
		this.setState(() => ({
			featureInfo: {
				properties: {
					displayName:"Grados",
					featureColumn:"Grados",
					Grados:value
					
				}
			}
		}));
	}

    handleMapClick(event) {
        this.setState(() => ({
            clickLocation: {
                lat: event.latlng.lat,
                lng: event.latlng.lng
            }
		}));
		
		if(this.state.rasterOn){
			fetchData('getRasterValue',{lat:event.latlng.lat, lng:event.latlng.lng}).then(returnData => { 
				this.setRasterValue((parseFloat(returnData)).toPrecision(2))
			})
		}
	}
	
	

    handleSpeciesChange(value) {
        let max = defaultmax[`${this.state.mapSettings.distinctOrTotal}_${value}`];
        max = max < 6 ? 6 : max;
        this.setState(prevState => ({
            mapSettings: {
                distinctOrTotal: prevState.mapSettings.distinctOrTotal,
                myObsType: value,
                fillOpacity: prevState.mapSettings.fillOpacity,
                maxValue: max
            }
        }));
    }

    handleTotalDistinctChange(value) {
        let max = defaultmax[`${value}_${this.state.mapSettings.myObsType}`];
        max = max < 6 ? 6 : max;
        this.setState(prevState => ({
            mapSettings: {
				...prevState.mapSettings,
                distinctOrTotal: value,
                maxValue: max
            }
        }));
    }

    setDefaultMax(max) {
        max = max < 6 ? 6 : max;
        this.setState(prevState => ({
            mapSettings: {
				...prevState.mapSettings,
                maxValue: max
            }
        }));
    }

    handleFeatureClick(event) {
		if(!this.state.rasterOn){
			const idtype =
				event.target.feature.properties.name == "udp_puebla_4326"
					? "udp"
					: event.target.feature.properties.name == "linea_mtp"
					? "linea_mtp"
					:"other";

			this.setState(() => ({
				udpButton: idtype == "udp" ? true : false
			}));
			this.setState(() => ({
				lineaButton: idtype == "linea_mtp" ? true : false
			}));


			this.setState(() => ({
				currentLineaId: event.target.feature.properties.iden
			}));

			this.setState(() => ({
				currentUdpId: event.target.feature.properties.iden
			}));

			let myColor = "green";
			let myWeight = 5;
			let myOpacity = 5;

			if (this.state.previous) {
				something.forEach(thing => {
					if (
						thing.tableName ==
						this.state.previous.feature.properties.name
					) {
						myColor = thing.color;
						myWeight = thing.weight;
						myOpacity = thing.opacity;
					}
				});

				if ( this.state.previous.feature.properties.name == "udp_puebla_4326" ) {
					this.state.previous.setStyle({
						weight: this.getOutline(
							this.state.previous.feature.properties,
							"weight"
						),
						color: this.getOutline(
							this.state.previous.feature.properties,
							"color"
						),
						opacity: myOpacity
					});
				} else {
					this.state.previous.setStyle({
						color: myColor,
						weight: myWeight,
						opacity: myOpacity
					});
				}
			}
			this.setState(() => ({
				previous: event.target
			}));

			var highlight = {
				color: "yellow",
				weight: 3,
				opacity: 1
			};
			event.target.setStyle(highlight);
			
			this.setState(() => ({
				featureInfo: {
					properties: event.target.feature.properties
				}
			}));
		}
    }

    handleOpacityChange(value) {
        this.setState(prevState => ({
            mapSettings: {
                ...prevState.mapSettings,
                fillOpacity: value,
            }
        }));
    }

    handleMaxChange(value) {
        this.setState(prevState => ({
            mapSettings: {
				...prevState.mapSettings,
                maxValue: value
            }
        }));
    }

    render() {
        return (
            <div id="mappagediv">
                <div id="pagecontainer">
                    <div id="mapdiv" className="border border-dark">
                        <Map
                            getOutline={this.getOutline}
                            handleMapClick={this.handleMapClick}
                            handleFeatureClick={this.handleFeatureClick}
                            setDefaultMax={this.setDefaultMax}
							mapSettings={this.state.mapSettings}
							handleOverlayChange={this.handleOverlayChange}
                        />
                    </div>

                    <div id="mapinfodisplay">
                        <FeatureInfoDisplay
                            clickLocation={this.state.clickLocation}
                            featureInfo={this.state.featureInfo}
                            clicked={this.state.clickLocation.lat != 999.9}
                        />
                    </div>
                    <div id="mapcontrol">
                        {false && <MapControl
                            handleSpeciesChange={this.handleSpeciesChange}
                            handleTotalDistinctChange={
                                this.handleTotalDistinctChange
                            }
                            handleOpacityChange={this.handleOpacityChange}
                            handleMaxChange={this.handleMaxChange}
                            mapSettings={this.state.mapSettings}
                        />}
                    </div>

					<div id="buttons1">
					{this.state.lineaButton && (
						<div>
							<a
								className="btn btn-primary m-2 btn-sm mapInfoButton"
								href={
									"/mostrarnormas/ae/" + 
									this.state.currentLineaId+'l'
								}
								role="button"
							>
								{" "}
								Atributos Ecologicos{" "}
							</a>
							<a
								className="btn btn-primary m-2 btn-sm mapInfoButton"
								href={
									"/mostrarnormas/normas/" +
									this.state.currentUdpId +'l'
								}
								role="button"
							>
								{" "}
								Especies y Normas 059
							</a>
						</div>
						
					)}
                        {false && this.state.udpButton && (
							<div>
							<div>
							<a
								className="btn btn-primary m-2 btn-sm mapInfoButton"
								href={
									"/mostrarnormas/ae/" +
									this.state.currentUdpId +'u'
								}
								role="button"
							>
								{" "}
								Attributos Ecologicos{" "}
							</a>
							<a
							className="btn btn-primary m-2 btn-sm mapInfoButton"
							href={
								"/mostrarnormas/normas/" +
								this.state.currentUdpId +'u'
							}
							role="button"
							>
							{" "}
							Especies y Normas 059
							</a>
							
							<a
								className="btn btn-primary m-2 btn-sm mapInfoButton"
								href={
									"/udpmapa/inf/" +
									this.state.currentUdpId +
									"/" +
									`${
										this.state.featureInfo.properties
											.shannon_arbol
									}*${
										this.state.featureInfo.properties
											.shannon_arbusto
									}*${
										this.state.featureInfo.properties
											.shannon_ave
									}*${
										this.state.featureInfo.properties
											.shannon_hierba
									}*${
										this.state.featureInfo.properties
											.shannon_herpetofauna
									}*${
										this.state.featureInfo.properties
											.shannon_mamifero
									}`
								}
								role="button"
							>
								{" "}
								Infrastructura{" "}
							</a>
							</div>
							<div>
							
							<a
								className="btn btn-primary m-2 btn-sm mapInfoButton"
								href={
									"/mostrarnormas/in/" +
									this.state.currentUdpId
								}
								role="button"
							>
							{" "}
							Instrumentos de Gestion Territorial{" "}
						</a>
							
                                <a
                                    className="btn btn-primary m-2 btn-sm mapInfoButton"
                                    href={
                                        "/udpmapa/sue/" +
                                        this.state.currentUdpId +
                                        "/" +
                                        `${
                                            this.state.featureInfo.properties
                                                .shannon_arbol
                                        }*${
                                            this.state.featureInfo.properties
                                                .shannon_arbusto
                                        }*${
                                            this.state.featureInfo.properties
                                                .shannon_ave
                                        }*${
                                            this.state.featureInfo.properties
                                                .shannon_hierba
                                        }*${
                                            this.state.featureInfo.properties
                                                .shannon_herpetofauna
                                        }*${
                                            this.state.featureInfo.properties
                                                .shannon_mamifero
                                        }`
                                    }
                                    role="button"
                                >
                                    {" "}
                                    Fragmentación Ambiental{" "}
                                </a>
								
							</div>
                            </div>
							
                        )}
                    </div> 
                </div>
            </div>
        );
    }
}

export default Mapapp;
