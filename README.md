# WebClient.PHP

WebClient.PHP is a PHP class based on the php_curl extension that helps navigating remote websites.

## Examples

- Example 1
```php
  <?php
  require_once('./src/WebClient.php');

  $webClient = new WebClient();
  
  if( !($html = $webClient->Navigate( "http://example.com/" )) ) {
    trigger_error( "Could not load the index page.", E_USER_ERROR );  
  }
  
  $postArray = $webClient->getInputs();
  $postArray["username"] = "bagia";
  $postArray["password"] = "password";
  
  if( !($html = $webClient->Navigate( "http://example.com/login.php", $postArray )) ) {
    trigger_error( "Could not load the login page.", E_USER_ERROR );  
  }
  
  echo $html;
  ?>
```

## TODO List

### getInputs()
- handle multiple form tags
- handle other user input tags such as textarea
