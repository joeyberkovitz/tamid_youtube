import {Injectable} from '@angular/core';
import {HttpEvent, HttpHandler, HttpInterceptor, HttpRequest} from '@angular/common/http';
import {Observable} from 'rxjs';
import {UserService} from '../user/user.service';
import {environment} from '../../environments/environment';

@Injectable()
export class AuthInterceptor implements HttpInterceptor{
  constructor(private userService: UserService) {}

  intercept(req: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
    // Skip if no token or if calling an auth related URL
    if (!this.userService.isAuthed()
      || req.url === environment.apiUrl + '/oauth/token' || req.url === environment.apiUrl + '/api/register')
      return next.handle(req);

    // Add access token to request
    const authedReq = req.clone({
      headers: req.headers.set('Authorization', 'Bearer ' + this.userService.getAuthToken())
    });

    // Todo: handle token refresh case if needed
    return next.handle(authedReq);
  }
}
