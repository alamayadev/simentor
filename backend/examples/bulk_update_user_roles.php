<?php

/**
 * Example usage of the bulk update user roles API endpoint
 *
 * This script demonstrates how to use the POST /api/users/bulk-update-roles endpoint
 * to update roles for multiple users at once.
 */

// Example 1: Assign "mitra" role to multiple users
$example1 = [
    'user_ids' => [2, 3, 4, 5],
    'roles' => ['mitra']
];

echo "Example 1: Assign 'mitra' role to users with IDs 2, 3, 4, 5\n";
echo json_encode($example1, JSON_PRETTY_PRINT) . "\n\n";

// Example 2: Assign multiple roles to a single user
$example2 = [
    'user_ids' => [6],
    'roles' => ['mitra', 'operator']
];

echo "Example 2: Assign 'mitra' and 'operator' roles to user with ID 6\n";
echo json_encode($example2, JSON_PRETTY_PRINT) . "\n\n";

// Example 3: Update roles for multiple users with multiple roles
$example3 = [
    'user_ids' => [7, 8, 9],
    'roles' => ['operator', 'supervisor']
];

echo "Example 3: Assign 'operator' and 'supervisor' roles to users with IDs 7, 8, 9\n";
echo json_encode($example3, JSON_PRETTY_PRINT) . "\n\n";

// Example cURL command
$curlExample = '
# Example cURL command to bulk update user roles
curl -X POST "http://127.0.0.1:8000/api/users/bulk-update-roles" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json" \
  -d \'{
    "user_ids": [2, 3, 4],
    "roles": ["mitra", "operator"]
  }\'
';

echo "Example cURL command:\n";
echo $curlExample . "\n";

?>
