import {EventEmitter, Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {environment} from '../../environments/environment';
import {Observable, throwError} from 'rxjs';
import {catchError, concatMap, map} from 'rxjs/operators';

export interface User {
  name: string;
  email: string;
  password: string;
}

export interface UserToken {
  access_token: string;
  refresh_token: string;
  token_type: string;
  expires_in: number;
}


@Injectable({
  providedIn: 'root'
})
export class UserService {
  public loginCompleteEmitter$: EventEmitter<any>;

  constructor(private http: HttpClient) {
    // Emitter which sends out an event whenever a user logs in
    this.loginCompleteEmitter$ = new EventEmitter<any>();
  }

  login(username, password): Observable<boolean>{
    return this.http.post<UserToken>(environment.apiUrl + '/oauth/token', {
      grant_type: 'password',
      client_id: environment.clientId,
      // This isn't meant to be secret, just needed by Passport server
      client_secret: environment.clientSecret,
      username,
      password,
      scope: '*'
    }).pipe(
      catchError(err => {
        return throwError(err);
      }),
      map(
      data => {
        localStorage.setItem('access_token', data.access_token);
        localStorage.setItem('refresh_token', data.refresh_token);
        this.loginCompleteEmitter$.emit(true);
        return true;
      })
    );
  }

  register(username, name, password): Observable<boolean>{
    return this.http.post<UserToken>(environment.apiUrl + '/api/register', {
      name,
      email: username,
      password
    }).pipe(
      catchError(err => {
        return throwError(err);
      }),
      concatMap(data => this.login(username, password))
    );
  }

  isAuthed(): boolean{
    // Authed if there is an auth token in local storage
    return !!localStorage.getItem('access_token');
  }

  logout(): void{
    localStorage.removeItem('access_token');
    localStorage.removeItem('refresh_token');
    // Todo: Maybe call Oauth endpoint to revoke token
  }

  getAuthToken(): string{
    return localStorage.getItem('access_token');
  }
}
