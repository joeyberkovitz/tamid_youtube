import {EventEmitter, Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable} from 'rxjs';
import {environment} from '../../environments/environment';
import {map} from "rxjs/operators";

export interface SearchItem{
  kind: string;
  etag: string;
  id: {
    kind: string,
    videoId: string
  };
  snippet: {
    publishedAt: string; // Datetime
    channelId: string;
    title: string;
    description: string;
    thumbnails: {
      [key: string]: {
        url: string;
        width: number;
        height: number;
      }
    };
    channelTitle: string;
    liveBroadcastContent: string;
    publishTime: string;
  };
}

export interface SearchResult {
  info: {
    kind: string;
    etag: string;
    nextPageToken: string;
    prevPageToken: string;
    resultsPerPage: number;
    totalResults: number;
  };
  results: SearchItem[];
}

export interface SearchHistory {
  query: string;
}

@Injectable({
  providedIn: 'root'
})
export class SearchService {
  public searchCompleteEmitter$: EventEmitter<any>;

  constructor(private http: HttpClient) {
    this.searchCompleteEmitter$ = new EventEmitter<any>();
  }

  search(query: string): Observable<SearchResult>{
    return this.http.get<SearchResult>(environment.apiUrl + '/api/search?query=' + query).pipe(map(
      data => {
        this.searchCompleteEmitter$.emit(true);
        return data;
      }
    ));
  }

  searchHistory(): Observable<SearchHistory[]>{
    return this.http.get<SearchHistory[]>(environment.apiUrl + '/api/search/history');
  }
}
