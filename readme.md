# Biodiversity in Puebla

This project provides a web platform to capture, analyze, and display biodiversity data in the state of Puebla, Mexico. The web platform provides an intuitive user interface for field technicians throughout the state to enter data collected via the MTP biodiversity monitoring protocol. The site is live at
* [https://biodiversidadpuebla.online](https//:biodiversidadpuebla.online). If you would like visitor login permission, send me and email at forest.carter@gmail.com.

### User Interface

The user interface was custom built to be intuitive and user friendly. Users can enter data manually or by uploading an Excel document. Existing data is automatically retrieved to be editable. Extensive data verification provides data integrity and consistency.
This section is under the 'Ingresar Datos' heading.


### Mapping

Spatial data mapping was done using Leaflet. This page also uses React to provide a responsive user experience. JavaScript Fetch API is used to generate dynamic SQL queries which retrieve relevant data. Extensive analysis capabilities are provided on the scale on individual measurement lines and on 25 square kilometer quadrants. Python and GDAL tools were used to tile raster data. This section is under the 'Mostrar Mapas' heading.

### Database

All data is stored in a relational database using PostgreSQL, running on a cloud based virtual machine. Spatial data is handled using the POSTGIS extension.  

### Development and Framework

Laravel was used to scaffold the views using Blade and PHP. Git and Github provided versioning. React, Leaflet, Bootstrap, and Sass were also utilized.  


### Server

The website runs on a Debian 9 (Stretch) droplet from Digital Ocean. Encryption is done using LetsEncrypt and Certbot. Apache2 serves the site to the web. 





## Authors

Jesús Hernández Castán

* **Jesús Hernández Castán** - *Project Director* - [LinkedIn](https://www.linkedin.com/in/jes%C3%BAs-hern%C3%A1ndez-cast%C3%A1n-144752ab/?originalSubdomain=mx)

* **Forest Carter** - *Developer* - [Portfolio](https://forestcarter.github.io/)
