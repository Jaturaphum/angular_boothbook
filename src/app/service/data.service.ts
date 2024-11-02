import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class DataService {
  apiEndpoint='http://wag12.bowlab.net/api';
  constructor() { }
}