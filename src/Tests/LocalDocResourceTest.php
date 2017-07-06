<?php

namespace Drupal\relaxed\Tests;

/**
 * Tests the /db/doc resource.
 *
 * @group relaxed
 */
class LocalDocResourceTest extends ResourceTestBase {

  public function testHead() {
    // HEAD and GET is handled by the same resource.
    $entity_types = ['entity_test_local'];

    foreach ($entity_types as $entity_type) {
      // Create a user with the correct permissions.
      $permissions = $this->entityPermissions($entity_type, 'view');
      $permissions[] = 'administer workspaces';
      $permissions[] = 'perform pull replication';
      $account = $this->drupalCreateUser($permissions);
      $this->drupalLogin($account);

      // We set this here just for testing.
      $this->multiversionManager->setActiveWorkspaceId($this->workspace->id());

      $entity = $this->entityTypeManager->getStorage($entity_type)->create();
      $entity->save();
      $this->httpRequest("$this->dbname/_local/" . $entity->uuid(), 'HEAD', NULL);
      $this->assertHeader('content-type', $this->defaultMimeType);
      $this->assertResponse('200', 'HTTP response code is correct.');
    }

    // Test with an entity type that is not local.
    $entity = $this->entityTypeManager->getStorage('entity_test_rev')->create();
    $entity->save();
    $this->httpRequest("$this->dbname/_local/" . $entity->uuid(), 'HEAD', NULL);
    $this->assertHeader('content-type', $this->defaultMimeType);
    $this->assertResponse('400', 'HTTP response code is correct.');
  }

  public function testGet() {
    $entity_types = ['entity_test_local'];
    foreach ($entity_types as $entity_type) {
      // Create a user with the correct permissions.
      $permissions = $this->entityPermissions($entity_type, 'view');
      $permissions[] = 'administer workspaces';
      $permissions[] = 'perform pull replication';
      $account = $this->drupalCreateUser($permissions);
      $this->drupalLogin($account);

      // We set this here just for testing.
      $this->multiversionManager->setActiveWorkspaceId($this->workspace->id());

      $entity = $this->entityTypeManager->getStorage($entity_type)->create();
      $entity->save();
      $this->httpRequest("$this->dbname/_local/" . $entity->uuid(), 'GET', NULL);
      $this->assertResponse('200', 'HTTP response code is correct.');
    }

    // Test with an entity type that is not local.
    $entity = $this->entityTypeManager->getStorage('entity_test_rev')->create();
    $entity->save();
    $this->httpRequest("$this->dbname/_local/" . $entity->uuid(), 'GET', NULL);
    $this->assertHeader('content-type', $this->defaultMimeType);
    $this->assertResponse('400', 'HTTP response code is correct.');
  }

  public function testPut() {
    $serializer = $this->container->get('replication.serializer');

    $entity_types = ['entity_test_local'];

    foreach ($entity_types as $entity_type) {
      // Create a user with the correct permissions.
      $permissions = $this->entityPermissions($entity_type, 'create');
      $permissions[] = 'administer workspaces';
      $permissions[] = 'perform push replication';
      $account = $this->drupalCreateUser($permissions);
      $this->drupalLogin($account);

      // We set this here just for testing.
      $this->multiversionManager->setActiveWorkspaceId($this->workspace->id());

      $entity = $this->entityTypeManager->getStorage($entity_type)->create(['user_id' => $account->id()]);
      $serialized = $serializer->serialize($entity, $this->defaultFormat);
      $this->httpRequest("$this->dbname/_local/" . $entity->uuid(), 'PUT', $serialized);
      $this->assertResponse('201', 'HTTP response code is correct');
    }

    // Create a user with the correct permissions.
    $permissions = $this->entityPermissions('entity_test_rev', 'create');
    $permissions[] = 'administer workspaces';
    $permissions[] = 'perform push replication';
    $account = $this->drupalCreateUser($permissions);
    $this->drupalLogin($account);

    // Test with an entity type that is not local.
    $entity = $this->entityTypeManager->getStorage('entity_test_rev')->create();
    $serialized = $serializer->serialize($entity, $this->defaultFormat);
    $response = $this->httpRequest("$this->dbname/_local/" . $entity->uuid(), 'PUT', $serialized);
    $this->assertEqual('', $response);
    $this->assertHeader('content-type', $this->defaultMimeType);
    $this->assertResponse('400', 'HTTP response code is correct.');
  }

}
