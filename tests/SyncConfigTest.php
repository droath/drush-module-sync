<?php

namespace Droath\ModuleSync\Tests;

use Droath\ModuleSync\SyncConfig;
use PHPUnit\Framework\TestCase;

/**
 * Define sync configuration unit test.
 */
class SyncConfigTest extends TestCase {

  protected $syncConfig;

  public function setUp() {
    $this->syncConfig = new SyncConfig(
      __DIR__ . '/fixtures/module-installation.yml'
    );
  }

  public function testGetVersion() {
    $this->assertEquals(1, $this->syncConfig->getVersion());
  }

  public function testGetScopes() {
    $this->assertEquals(['stage', 'local'], $this->syncConfig->getScopes());
  }

  public function testHasScope() {
    $this->assertTrue($this->syncConfig->hasScope('local'));
    $this->assertFalse($this->syncConfig->hasScope('nonexistent'));
  }

  public function testGetExcludeTypes() {
    $this->assertEquals(['profile'], $this->syncConfig->getExcludeTypes());
  }

  public function testGetModulesByScope() {
    $modules = $this->syncConfig->getModulesByScope('stage');
    $this->assertContains('file_stage_proxy', $modules);
  }

  /**
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage Invalid scope has been passed.
   */
  public function testGetMouldesByScopeWithNonExistentScope() {
    $this->syncConfig->getModulesByScope('nonexistent');
  }

}
