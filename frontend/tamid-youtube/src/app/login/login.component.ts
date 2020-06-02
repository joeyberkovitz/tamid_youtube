import {Component, OnInit, ViewChild} from '@angular/core';
import {NgForm} from '@angular/forms';
import {UserService} from '../user/user.service';
import {MatSnackBar} from '@angular/material/snack-bar';
import {Router} from '@angular/router';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.scss']
})
export class LoginComponent implements OnInit {
  public username: string = null;
  public password: string = null;
  public showPw = false;
  // Form type; true = Login, false = Register
  public formType = true;
  // User's name - only relevant for registration form
  public name: string = null;
  private submitting = false;

  @ViewChild('loginForm') form: NgForm;

  constructor(private userService: UserService, private snackBar: MatSnackBar, private router: Router) { }

  ngOnInit(): void {
  }

  buttonDisabled(): boolean{
    return (this.form && !this.form.valid) || this.submitting;
  }

  showPassword(): string{
    return this.showPw ? 'text' : 'password';
  }

  submit(): void{
    // If form invalid, don't submit
    // This should never occur, so no need to provide a user facing error
    if (this.form.invalid){
      console.log('Form is invalid, skipping');
      return;
    }
    this.submitting = true;

    let req;
    // If login form, attempt login
    if (this.formType){
      req = this.userService.login(this.username, this.password);
    }
    else{
      // After register, login occurs, so same callbacks can be used
      req = this.userService.register(this.username, this.name, this.password);
    }

    req.subscribe(
      data => {
        // On login success, redirect to home page
        if (data){
          this.router.navigate(['/']);
        }
        this.submitting = false;
      },
      error => {
        this.submitting = false;
        let errorMessage = error && error.error && error.error.description ?
          error.error.error_description : 'An error occurred, please try again later';
        if (error.error.error === 'invalid_grant')
          errorMessage = 'Invalid Credentials';
        if (error.message)
          errorMessage = error.error.message;

        this.snackBar.open(errorMessage, '', {duration: 3000});
      }
    );
  }

}
