import { Component, OnInit, ViewChild, ElementRef } from "@angular/core";
import * as L from "leaflet";
import { GeoLocationService } from "../geo-location.service";
import { take } from "rxjs/operators";

class ApiResponseType {
  google_maps?: { longitude: number; latitude: number };
  osm_nominatim?: { longitude: number; latitude: number };
}

@Component({
  selector: "app-map",
  templateUrl: "./map.component.html",
  styleUrls: ["./map.component.css"],
})
export class MapComponent implements OnInit {
  @ViewChild("googleMap", { static: true }) gmap: ElementRef;

  public gLat = 42.698334;
  public gLng = 23.319941;
  public oLat = 42.698334;
  public oLng = 23.319941;

  private map: google.maps.Map;
  private oMap: L.Map;
  private coordinates = new google.maps.LatLng(this.gLat, this.gLng);
  private mapOptions: google.maps.MapOptions;
  private marker;
  private API_RES: ApiResponseType;
  address = "";
  error = "";

  constructor(private geoLocationService: GeoLocationService) {
    this.geoLocationService.getCurrentPos().subscribe((pos: Position) => {
      this.gLat = pos.coords.latitude;
      this.gLng = pos.coords.longitude;
      this.oLat = pos.coords.latitude;
      this.oLng = pos.coords.longitude;
    });
  }

  ngOnInit(): void {
    this.autocompleteSearch();
    this.setGoogleMapOptions();
    this.googleMapInitializer();
    this.setOpenMapOptions();
  }

  async getLocation() {
    const address = document.getElementById("place") as HTMLInputElement;

    this.API_RES = await this.geoLocationService
      .getAddress(address.value)
      .pipe(take(1))
      .toPromise();

    if (
      this.API_RES.google_maps == null ||
      this.API_RES.osm_nominatim == null
    ) {
      this.error = "Please provide valid address!";
      return;
    } else {
      this.error = '';
    }

    this.gLat = this.API_RES.google_maps.latitude;
    this.gLng = this.API_RES.google_maps.longitude;
    this.oLat = this.API_RES.osm_nominatim.latitude;
    this.oLng = this.API_RES.osm_nominatim.longitude;

    this.setGoogleMapOptions();
    this.oMap.remove();
    this.googleMapInitializer();
    this.setOpenMapOptions();
  }

  // Google Maps Implementation
  private googleMapInitializer() {
    this.map = new google.maps.Map(this.gmap.nativeElement, this.mapOptions);
    this.marker.setMap(this.map);
  }

  private setGoogleMapOptions() {
    this.coordinates = new google.maps.LatLng(this.gLat, this.gLng);
    this.mapOptions = {
      center: this.coordinates,
      zoom: 12,
    };
    this.marker = new google.maps.Marker({
      position: this.coordinates,
      map: this.map,
    });
  }

  private autocompleteSearch() {
    const place = document.getElementById("place") as HTMLInputElement;
    this.address = place.value;
    console.log(place.value);
    const autocomplete = new google.maps.places.Autocomplete(place);
    autocomplete.setTypes(["geocode"]);
  }

  // Open Street Map Implementation
  private setOpenMapOptions() {
    this.oMap = L.map("openStreetMap").setView([this.oLat, this.oLng], 12);

    L.tileLayer("http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      maxZoom: 18,
      attribution:
        '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors',
    }).addTo(this.oMap);

    L.marker([this.oLat, this.oLng]).addTo(this.oMap);
  }
}
