<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\Config;
use App\Service\LdapManager;
use Mockery as m;
use App\Service\Directory;
use App\Tests\TestCase;

class DirectoryTest extends TestCase
{
    protected $ldapManager;
    protected $config;
    protected $obj;

    public function setUp(): void
    {
        parent::setUp();
        $this->ldapManager = m::mock(LdapManager::class);
        $this->config = m::mock(Config::class);
        $this->obj = new Directory(
            $this->ldapManager,
            $this->config
        );
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->obj);
        unset($this->ldapManager);
        unset($this->config);
    }

    /**
     * @covers \App\Service\Directory::__construct
     */
    public function testConstructor()
    {
        $this->assertTrue($this->obj instanceof Directory);
    }

    /**
     * @covers \App\Service\Directory::findByCampusId
     */
    public function testFindByCampusId()
    {
        $this->config->shouldReceive('get')->once()->with('ldap_directory_campus_id_property')->andReturn('campusId');
        $this->ldapManager->shouldReceive('search')->with('(campusId=1234)')->andReturn([['id' => 1]]);

        $result = $this->obj->findByCampusId(1234);
        $this->assertSame($result, ['id' => 1]);
    }

    /**
     * @covers \App\Service\Directory::findByCampusId
     */
    public function testFindByCampusIds()
    {
        $this->config->shouldReceive('get')->once()->with('ldap_directory_campus_id_property')->andReturn('campusId');
        $this->ldapManager->shouldReceive('search')
            ->with('(|(campusId=1234)(campusId=1235))')->andReturn([['id' => 1], ['id' => 2]]);

        $result = $this->obj->findByCampusIds([1234, 1235]);
        $this->assertSame($result, [['id' => 1], ['id' => 2]]);
    }

    /**
     * @covers \App\Service\Directory::findByCampusId
     */
    public function testFindByCampusIdsOnlyUseUnique()
    {
        $this->config->shouldReceive('get')->once()->with('ldap_directory_campus_id_property')->andReturn('campusId');
        $this->ldapManager->shouldReceive('search')
            ->with(m::mustBe('(|(campusId=1234)(campusId=1235))'))->andReturn([1]);

        $result = $this->obj->findByCampusIds([1234, 1235, 1234, 1235]);
        $this->assertSame($result, [1]);
    }

    /**
     * @covers \App\Service\Directory::findByCampusId
     */
    public function testFindByCampusIdsInChunks()
    {
        $this->config->shouldReceive('get')->once()->with('ldap_directory_campus_id_property')->andReturn('campusId');
        $ids = [];
        $firstFilters = '(|';
        for ($i = 0; $i < 50; $i++) {
            $ids[] = $i;
            $firstFilters .= "(campusId={$i})";
        }
        $firstFilters .= ')';

        $secondFilters = '(|';
        for ($i = 50; $i < 100; $i++) {
            $ids[] = $i;
            $secondFilters .= "(campusId={$i})";
        }
        $secondFilters .= ')';

        $this->ldapManager->shouldReceive('search')
            ->with($firstFilters)->andReturn([1])->once();
        $this->ldapManager->shouldReceive('search')
            ->with($secondFilters)->andReturn([2])->once();

        $result = $this->obj->findByCampusIds($ids);
        $this->assertSame($result, [1, 2]);
    }

    /**
     * @covers \App\Service\Directory::find
     */
    public function testFind()
    {
        $this->config->shouldReceive('get')->once()->with('ldap_directory_campus_id_property')->andReturn('campusId');
        $filter = '(&(|(sn=a*)(givenname=a*)(mail=a*)(campusId=a*))(|(sn=b*)(givenname=b*)(mail=b*)(campusId=b*)))';
        $this->ldapManager->shouldReceive('search')->with($filter)->andReturn([1,2]);

        $result = $this->obj->find(['a', 'b']);
        $this->assertSame($result, [1,2]);
    }

    /**
     * @covers \App\Service\Directory::find
     */
    public function testFindOutputEscaping()
    {
        $this->config->shouldReceive('get')->once()->with('ldap_directory_campus_id_property')->andReturn('campusId');
        $filter = '(&(|(sn=a\2a*)(givenname=a\2a*)(mail=a\2a*)(campusId=a\2a*)))';
        $this->ldapManager->shouldReceive('search')->with($filter)->andReturn([1,2]);

        $result = $this->obj->find(['a*']);
        $this->assertSame($result, [1,2]);
    }

    /**
     * @covers \App\Service\Directory::findByLdapFilter
     */
    public function testFindByLdapFilter()
    {
        $filter = '(one)(two)';
        $this->ldapManager->shouldReceive('search')->with($filter)->andReturn([1,2]);

        $result = $this->obj->findByLdapFilter($filter);
        $this->assertSame($result, [1,2]);
    }
}
