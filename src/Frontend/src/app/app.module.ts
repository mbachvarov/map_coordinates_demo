import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { HttpClientModule } from '@angular/common/http';
import { AppComponent } from './app.component';
import { MapComponent } from './map/map.component';

import { AgmCoreModule } from '@agm/core';
import { LeafletModule } from '@asymmetrik/ngx-leaflet';
import { GeoLocationService } from './geo-location.service';

@NgModule({
  declarations: [
    AppComponent,
    MapComponent,
  ],
  imports: [
    HttpClientModule,
    BrowserModule,
    FormsModule, LeafletModule, AgmCoreModule.forRoot({
      apiKey: 'AIzaSyCTGWqk80D7DoLWioJ7avXWxgV1v-jc3wg'
    })],
  providers: [GeoLocationService],
  bootstrap: [AppComponent]
})
export class AppModule { }
