<?php

function newKey() {
  $result = date('Ymd_His') . '_' . substr((string)microtime(), 2, 8);
  return md5($result); // ENCRYPTION_KEY);
}
