import {Component, OnInit, ViewChild} from '@angular/core';
import {ActivatedRoute} from '@angular/router';
import {SearchService, SearchResult, SearchItem} from '../search/search.service';
import {YouTubePlayer} from '@angular/youtube-player';

@Component({
  selector: 'app-home',
  templateUrl: './home.component.html',
  styleUrls: ['./home.component.scss']
})
export class HomeComponent implements OnInit {
  public query: string;
  public searchResults: SearchResult;
  public currVideo: SearchItem;

  @ViewChild('yt') public yt: YouTubePlayer;

  constructor(private route: ActivatedRoute, private searchService: SearchService) {

  }

  ngOnInit(): void {
    // Youtube API requires a script be added dynamically
    const tag = document.createElement('script');

    tag.src = 'https://www.youtube.com/iframe_api';
    document.body.appendChild(tag);

    // Get search query from URL whenever it changes
    this.route.paramMap.subscribe(params => {
      this.query = params.get('query');
      this.search();
    });
  }

  search(): void{
    // tslint:disable-next-line:triple-equals
    if (this.query == '') return;

    this.searchService.search(this.query).subscribe(data => {
      this.searchResults = data;
      this.selectVideo(this.searchResults.results[0]);
    }, err => {
      console.log(err);
    });
  }

  selectVideo(video: SearchItem): void{
    if (this.yt) this.yt.stopVideo();

    this.currVideo = video;
    if (!this.yt){
      setTimeout(() => {this.selectVideo(video); }, 1000);
      return;
    }
    console.log('Playing video');
    setTimeout(() => {this.yt.playVideo(); }, 100);
  }
}
