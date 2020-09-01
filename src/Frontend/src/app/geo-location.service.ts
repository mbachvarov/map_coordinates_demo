import { Injectable } from "@angular/core";
import { Observable } from "rxjs";
import { HttpClient } from "@angular/common/http";

@Injectable({
  providedIn: "root",
})
export class GeoLocationService {
  private baseUrl =
    "https://src.sharedwithexpose.com/get_address_coordinates.php";

  constructor(private http: HttpClient) {}

  public getAddress(address): Observable<any> {
    return this.http.get(`${this.baseUrl}?address=${address}`, {
      withCredentials: false,
    });
  }

  public getCurrentPos(): Observable<any> {
    return new Observable((observer) => {
      navigator.geolocation.watchPosition((pos: Position) => {
        observer.next(pos);
      });
    });
  }
}
