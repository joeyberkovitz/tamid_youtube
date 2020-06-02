import {HTTP_INTERCEPTORS} from '@angular/common/http';
import {AuthInterceptor} from './auth-interceptor';

// Array that provides all HTTP interceptors
export const httpInterceptorProviders = [
  { provide: HTTP_INTERCEPTORS, useClass: AuthInterceptor, multi: true},
];
