import {Component, ViewChild} from '@angular/core';
import {UserService} from './user/user.service';
import {Router} from '@angular/router';
import {SearchHistory, SearchService} from './search/search.service';
import {MatAutocompleteTrigger} from '@angular/material/autocomplete';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.scss']
})
export class AppComponent {
  title = 'tamid-youtube';
  query: string;
  public searchHistory: SearchHistory[];
  @ViewChild(MatAutocompleteTrigger) auto: MatAutocompleteTrigger;

  constructor(private userService: UserService, private router: Router, private searchService: SearchService) {
    this.getSearchHistory();

    userService.loginCompleteEmitter$.subscribe(data => {
      this.getSearchHistory();
    });

    searchService.searchCompleteEmitter$.subscribe(data => {
      this.getSearchHistory();
    });
  }

  loggedIn(): boolean{
    return this.userService.isAuthed();
  }

  logout(): void{
    this.userService.logout();
  }

  search(): void{
    this.router.navigate(['/search', this.query]);
    this.auto.closePanel();
  }

  getSearchHistory(): void{
    if (!this.userService.isAuthed()) return;
    this.searchService.searchHistory().subscribe(
      data => {
        this.searchHistory = data;
      }, err => { console.log(err); });
  }
}
