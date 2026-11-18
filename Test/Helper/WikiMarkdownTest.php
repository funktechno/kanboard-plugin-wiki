<?php

require_once 'tests/units/Base.php';
require_once __DIR__ . '/../AssertMultipleTrait.php';
use KanboardTests\units\Base;
use Kanboard\Core\Plugin\Loader;
use Kanboard\Model\ProjectModel;
use Kanboard\Model\UserModel;
use Kanboard\Plugin\Wiki\Helper\WikiTextHelper;
use Kanboard\Plugin\Wiki\Model\WikiModel;
use Kanboard\Plugin\Wiki\Model\WikiFileModel;

class WikiMarkdownTest extends Base
{
    use AssertMultipleTrait;

    private $token;

    protected function setUp(): void
    {
        parent::setUp();

        $plugin = new Loader($this->container);
        $plugin->scan();

        // project 1: private, page 1 (active) and page 3 (archived); project 2: public, page 2
        $projectModel = new ProjectModel($this->container);
        $wikiModel = new WikiModel($this->container);
        $projectModel->create(array('name' => 'Private'));
        $projectModel->create(array('name' => 'Public'));
        $projectModel->enablePublicAccess(2);
        $this->token = $projectModel->getById(2)['token'];
        $wikiModel->createpage(1, 'Private page', 'content');
        $wikiModel->createpage(2, 'Public page', 'content');
        $wikiModel->createpage(1, 'Archived page', 'content');
        $this->container['db']->table(WikiModel::WIKITABLE)->eq('id', 3)->update(array('is_active' => 0));

        // files: 1 image and 2 document on private page 1, 3 image on public page 2
        foreach (array(array(1, 1), array(1, 0), array(2, 1)) as $row) {
            $this->container['db']->table(WikiFileModel::TABLE)->persist(array(
                'wikipage_id' => $row[0], 'name' => 'f', 'path' => 'wikipages/f', 'is_image' => $row[1],
                'date' => time(), 'user_id' => 1, 'size' => 1,
            ));
        }
    }

    private function src($controller, $action, array $params, $absolute = false)
    {
        $url = $this->container['helper']->url->to($controller, $action, array('plugin' => 'wiki') + $params, '', $absolute);
        return 'src="' . htmlspecialchars($url, ENT_QUOTES) . '"';
    }

    private function loginAs($user_id, $role)
    {
        $_SESSION['user'] = array('id' => $user_id, 'username' => 'user' . $user_id, 'role' => $role);
    }

    private function render($text, $isPublicLink = false)
    {
        return (new WikiTextHelper($this->container))->markdown($text, $isPublicLink);
    }

    private function href($controller, $action, array $params, $absolute = false)
    {
        $url = $this->container['helper']->url->to($controller, $action, array('plugin' => 'wiki') + $params, '', $absolute);
        return 'href="' . htmlspecialchars($url, ENT_QUOTES) . '"';
    }

    public function testLinksForProjectMember()
    {
        $this->loginAs(1, 'app-admin');

        $this->assertMultiple(
            fn() => $this->assertStringContainsString($this->href('WikiController', 'detail', array('project_id' => 1, 'wiki_id' => 1)), $this->render('[Docs](wiki:1)')),
            fn() => $this->assertStringContainsString('>Docs</a>', $this->render('[Docs](wiki:1)')),
            fn() => $this->assertStringContainsString($this->href('WikiController', 'detail', array('project_id' => 2, 'wiki_id' => 2)), $this->render('[Other](wiki:2)')),
        );
    }

    public function testNoLinkForMissingOrArchivedPage()
    {
        $this->loginAs(1, 'app-admin');

        $this->assertMultiple(
            fn() => $this->assertSame('<p><span>Gone</span></p>', $this->render('[Gone](wiki:999)')),
            fn() => $this->assertSame('<p><span>Old</span></p>', $this->render('[Old](wiki:3)')),
        );
    }

    public function testNoLinkWithoutProjectAccess()
    {
        $userModel = new UserModel($this->container);
        $userModel->create(array('username' => 'outsider', 'password' => 'password123'));
        $this->loginAs(2, 'app-user');

        $this->assertMultiple(
            fn() => $this->assertSame('<p><span>Docs</span></p>', $this->render('[Docs](wiki:1)')),
            fn() => $this->assertStringNotContainsString('wiki_id', $this->render('[Docs](wiki:1)')),
        );
    }

    public function testPublicLinks()
    {
        $this->loginAs(1, 'app-admin');

        $this->assertMultiple(
            fn() => $this->assertStringContainsString($this->href('WikiController', 'detail_readonly', array('token' => $this->token, 'wiki_id' => 2), true), $this->render('[Public](wiki:2)', true)),
            // a page of a project without public access gets no link, even for an admin
            fn() => $this->assertSame('<p><span>Private</span></p>', $this->render('[Private](wiki:1)', true)),
        );
    }

    public function testNoPublicLinkWhenPublicWikiIsOff()
    {
        $this->loginAs(1, 'app-admin');
        $this->container['configModel']->save(array('wiki_public_access' => 0));
        $this->container['memoryCache']->flush();

        $this->assertSame('<p><span>Public</span></p>', $this->render('[Public](wiki:2)', true));
    }

    public function testPlainReferences()
    {
        $this->loginAs(1, 'app-admin');
        $link = $this->href('WikiController', 'detail', array('project_id' => 1, 'wiki_id' => 1));

        $this->assertMultiple(
            fn() => $this->assertStringContainsString($link . ' class="wiki-link">Private page</a>', $this->render('See wiki:1 now')),
            fn() => $this->assertStringContainsString($link, $this->render('(wiki:1)')),
            // not a whole word, a missing page, or inside a link: left as text
            fn() => $this->assertSame('<p>mywiki:1 and wiki:1x</p>', $this->render('mywiki:1 and wiki:1x')),
            fn() => $this->assertSame('<p>wiki:999</p>', $this->render('wiki:999')),
            fn() => $this->assertStringNotContainsString('wiki-link', $this->render('[see wiki:1](https://kanboard.org)')),
            fn() => $this->assertSame('<p><code>wiki:1</code></p>', $this->render('`wiki:1`')),
        );
    }

    public function testPlainReferenceWithoutProjectAccess()
    {
        $userModel = new UserModel($this->container);
        $userModel->create(array('username' => 'outsider', 'password' => 'password123'));
        $this->loginAs(2, 'app-user');

        // no link and no page title for someone who cannot open the page
        $this->assertSame('<p>See wiki:1</p>', $this->render('See wiki:1'));
    }

    public function testFileLinksForProjectMember()
    {
        $this->loginAs(1, 'app-admin');

        $this->assertMultiple(
            fn() => $this->assertStringContainsString($this->src('WikiFileViewController', 'image', array('file_id' => 1)), $this->render('![diagram](wiki-file:1)')),
            fn() => $this->assertStringContainsString($this->href('WikiFileViewController', 'download', array('file_id' => 2)), $this->render('[spec](wiki-file:2)')),
            // a document cannot be embedded as an image: alt text only
            fn() => $this->assertSame('<p><span>doc</span></p>', $this->render('![doc](wiki-file:2)')),
            fn() => $this->assertSame('<p><span>gone</span></p>', $this->render('![gone](wiki-file:999)')),
        );
    }

    public function testNoFileLinksWithoutProjectAccess()
    {
        $userModel = new UserModel($this->container);
        $userModel->create(array('username' => 'outsider', 'password' => 'password123'));
        $this->loginAs(2, 'app-user');

        $this->assertMultiple(
            fn() => $this->assertSame('<p><span>diagram</span></p>', $this->render('![diagram](wiki-file:1)')),
            fn() => $this->assertSame('<p><span>spec</span></p>', $this->render('[spec](wiki-file:2)')),
        );
    }

    public function testPublicFileLinks()
    {
        $this->loginAs(1, 'app-admin');

        // public attachments are off by default
        $this->assertSame('<p><span>pic</span></p>', $this->render('![pic](wiki-file:3)', true));

        $this->container['configModel']->save(array('wiki_public_attachments' => 1));
        $this->container['memoryCache']->flush();

        $this->assertMultiple(
            fn() => $this->assertStringContainsString($this->src('WikiPublicFileController', 'image', array('token' => $this->token, 'file_id' => 3), true), $this->render('![pic](wiki-file:3)', true)),
            // a file of a project without public access stays hidden
            fn() => $this->assertSame('<p><span>diagram</span></p>', $this->render('![diagram](wiki-file:1)', true)),
        );
    }

    public function testCoreMarkdownIsUnchanged()
    {
        $this->loginAs(1, 'app-admin');

        $this->assertMultiple(
            fn() => $this->assertStringContainsString('href="https://kanboard.org"', $this->render('[Kanboard](https://kanboard.org)')),
            // safe mode still neutralizes script links
            fn() => $this->assertStringNotContainsString('href="javascript:', $this->render('[x](javascript:alert(1))')),
            // images are left to core: no page URL in an image source
            fn() => $this->assertStringNotContainsString('wiki_id', $this->render('![pic](wiki:1)')),
            fn() => $this->assertStringContainsString('<strong>bold</strong>', $this->render('**bold** text')),
        );
    }
}
