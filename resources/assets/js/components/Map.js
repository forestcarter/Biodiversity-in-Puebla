import React from "react";
import L from "leaflet";

const style = {
    height: "100%"
};

class Map extends React.Component {
    constructor(props) {
        super(props);
		this.getColor = this.getColor.bind(this);
		this.handleOverlayChange = this.handleOverlayChange.bind(this);

    }
    getColor(x) {
		return isNaN(x)? "#ffffff"
			: x < this.props.mapSettings.maxValue * (1 / 6)
            ? "#ccece6"
            : x < this.props.mapSettings.maxValue * (2 / 6)
            ? "#99d8c9"
            : x < this.props.mapSettings.maxValue * (3 / 6)
            ? "#66c2a4"
            : x < this.props.mapSettings.maxValue * (4 / 6)
            ? "#41ae76"
            : x < this.props.mapSettings.maxValue * (5 / 6)
            ? "#238b45"
            : x < this.props.mapSettings.maxValue
            ? "#006d2c"
            : "#00441b";
	}
	
	handleOverlayChange(name,type) {
		this.props.handleOverlayChange(name, type);
	  }

    componentDidMount() {
        this.props.setDefaultMax(
            defaultmax[
                `${this.props.mapSettings.distinctOrTotal}_${
                    this.props.mapSettings.myObsType
                }`
            ]
        );
        // create map
        this.map = L.map("map", {
            zoomSnap: 0.5,
            layers: []
        });

        const streets = L.tileLayer("https://a.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            attribution:
                '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
		}).addTo(this.map);
		streets['category']='Base'


        const imagery = L.tileLayer(
            "https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}",
            {
                attribution:
                    '&copy; <a href="http://www.esri.com/">Esri</a>i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community',
                maxZoom: 18
            }
        );
		imagery['category']='Base'

        this.baseMaps = {
            Imagery: imagery,
            Streets: streets
        };

        const get_shp = (item, mymap) => {
            let geojsonMarkerOptions = {
                radius: 4,
                fillColor: item.fillColor,
                color: item.color,
                weight: item.weight,
                opacity: item.opacity,
                fillOpacity: item.fillOpacity
            };

            let myStyle = {
                weight: item.weight,
                color: item.color,
                opacity: item.opacity,
                fillColor: item.fillColor,
                fillOpacity: item.fillOpacity
            };

            const onEachFeature = (feature, layer) => {
                const handleFeatureClick = event => {
                    this.props.handleFeatureClick(event);
                };
                layer.on("click", handleFeatureClick);
            };

            let c2 = L.geoJson(item.geom, {
                style: myStyle,
                onEachFeature: onEachFeature
            });
            if (item.geom && item.geom.features[0].geometry && item.geom.features[0].geometry.type == "Point") {
                c2 = L.geoJSON(item.geom, {
                    pointToLayer: function(feature, latlng) {
                        return L.circleMarker(latlng, geojsonMarkerOptions);
                    },
                    onEachFeature: onEachFeature,
                    style: myStyle
                });
            }
            if ( item.tableName == "linea_mtp" || item.tableName == "peru" ) {
                c2.addTo(mymap);
			}
			c2['category']=item.category
			c2['tableName']=item.tableName

			

            return c2;
        };

        const processArray = ( array, mymap, mybaseMaps, getColor, getOutline ) => {
            var dynamicLayer = "notset";
			const overlayMaps = this.overlayMaps || {};
			var actividadArray=[]
            array.forEach(function(item) {
                let myLayer = get_shp(item, mymap, getColor, getOutline);
                if (item.tableName == "peru") {
					dynamicLayer = myLayer;
					console.log(item)
                    mymap.fitBounds(myLayer.getBounds());
                    mymap.setZoom(4);
				}

				if (myLayer.category=='Referencial'){
					overlayMaps["Placeholder_Referencial"] = myLayer;
				}
				if (myLayer.category=='Monitoreo Activo'){
					overlayMaps["Placeholder_Monitoreo Activo"] = myLayer;
				}
				if (myLayer.category=='Gestion del Territorio'){
					overlayMaps["Placeholder_Gestion del Territorio"] = myLayer;
				}
				if(item.displayName.includes('Acciones_')){
					actividadArray.push(myLayer)
					if (actividadArray.length==2){
						const actividadLG = L.layerGroup(actividadArray)
						actividadLG['category'] = 'Monitoreo Activo'
						overlayMaps['Acciones'] =  actividadLG;
					}
				}else{
					overlayMaps[item.displayName] = myLayer; 
				}

			});

			var tempraster = L.tileLayer("temptiles/{z}/{x}/{y}.png", { enable: true, tms: true, opacity: 0.8, attribution: "" });
			tempraster.category='Referencial'
			//overlayMaps["Escenario85_2099_Temp_UNIATMOS_2015"] = tempraster;
			
			const compare =function(a, b) {
				if (a.category=='Base') {
					return -1;
				}
				const ascore = a.category=='Referencial'?15:a.category=='Monitoreo Activo'?10:5
				const abonus = a.category.includes('PlaceHolder')?1:0 
				const bscore = b.category=='Referencial'?15:b.category=='Monitoreo Activo'?10:5
				const bbonus = b.category.includes('PlaceHolder')?1:0 
				const afinalscore=ascore + abonus
				const bfinalscore=bscore + bbonus

				if (afinalscore>bfinalscore) {
				  return -1;
				}
				if (afinalscore<bfinalscore) {
					return 1;
				  }
				return 0;
			  }
			  const contolOptions={sortLayers:true, sortFunction:compare}
			L.control.layers(mybaseMaps, overlayMaps,contolOptions).addTo(mymap);
			
			const layerList=document.getElementsByClassName("leaflet-control-layers-overlays")[0].children
			for (let i = 0; i < layerList.length; i++) {
				if(layerList[i].innerText.includes("Placeholder")){
					const newText=(layerList[i].innerText.split("_")[1]).toUpperCase()
					layerList[i].innerHTML = `<div class=layerHeader>${newText}</div>`				
				}
			}
		

            return dynamicLayer;
        };
        this.dynamicLayer = processArray( something, this.map, this.baseMaps, this.getColor, this.getOutline );
        this.map.on("click", this.props.handleMapClick);
		this.map.scrollWheelZoom.disable();
		this.map.on('overlayadd', (eo)=> {
			this.handleOverlayChange(eo.name, eo.type)
			});
		this.map.on('overlayremove', (eo)=> {
			this.handleOverlayChange(eo.name, eo.type)
			});
	
	//Sus Datos Legend
        const susDatosLegend = L.control({ position: "bottomleft" });
        this.makeDiv = () => {
            var div = L.DomUtil.create("div", "info legend"),
                grades = [],
                labels = [];
            L.DomUtil.addClass(div, "colorLegend border border-secondary p-2");

            div.innerHTML +=
                '<i class="m-1" style="outline: 3px solid purple; background:white">&nbsp&nbsp&nbsp&nbsp</i> ' +
                "Sus datos <br><br>";
            div.innerHTML +=
                '<i class="m-1" style="outline: 3px solid red; background:white">&nbsp&nbsp&nbsp&nbsp</i> ' +
                "Datos de otros monitores<br><br>";
            div.innerHTML +=
                '<i class="m-1" style="outline: 3px solid yellow; background:white">&nbsp&nbsp&nbsp&nbsp</i> ' +
                "Selección<br>";
            return div;
        };
        susDatosLegend.onAdd = this.makeDiv;
        susDatosLegend.addTo(this.map);
        
        //Species number legend
        const speciesLegend = L.control({ position: "bottomright" });

        this.makeDiv = () => {
            grades = [];
            for (var i = 0; i <= 6; i++) {
                grades.push(this.props.mapSettings.maxValue * (i / 6)),
                    (labels = []);
            }
            const getColor = this.getColor;
            var div = L.DomUtil.create("div", "info legend"),
                grades,
                labels = [];
            L.DomUtil.addClass(div, "colorLegend border border-secondary");
            // loop through our density intervals and generate a label with a colored square for each interval
            for (var i = 0; i < grades.length; i++) {
                div.innerHTML +=
                    '<i style="background:' +
                    getColor(grades[i]) +
                    '">&nbsp&nbsp&nbsp&nbsp</i> ' +
                    Math.floor(grades[i]) +
                    (grades[i + 1]
                        ? "&ndash;" + Math.floor(grades[i + 1]) + "<br>"
                        : "+");
            }
            return div;
        };
        speciesLegend.onAdd = this.makeDiv;
        //speciesLegend.addTo(this.map);
        this.speciesLegend = speciesLegend;
        /////////////////////////////////////////////////////
    }

    componentDidUpdate({ mapSettings }) {
        if (false && this.props.mapSettings !== mapSettings) {
            this.map.removeControl(this.speciesLegend);
            const updatedLegend = L.control({ position: "bottomright" });
            updatedLegend.onAdd = this.makeDiv;
            updatedLegend.addTo(this.map);
            this.speciesLegend = updatedLegend;
            const getColor = this.getColor;
            const getOutline = this.props.getOutline;
            const targetProperty = `${this.props.mapSettings.distinctOrTotal}_${
                this.props.mapSettings.myObsType
            }`;

            const myStyle = (feature) => {
                return {
                    fillColor: getColor(feature.properties[targetProperty]),
                    opacity: 1,
                    weight: getOutline(feature.properties, "weight"),
                    color: getOutline(feature.properties, "color"),
                    fillOpacity: this.props.mapSettings.fillOpacity
                };
            };
            this.dynamicLayer.setStyle(myStyle);
        }
    }
    render() {
        return <div id="map" style={style} />;
    }
}
export default Map;
