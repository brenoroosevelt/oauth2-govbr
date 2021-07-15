<?php
declare(strict_types=1);

namespace BrenoRoosevelt\OAuth2\Client\Test;

use BrenoRoosevelt\OAuth2\Client\Avatar;

class GovBrUserTest extends TestCase
{
    /** @test */
    public function deveCriarAvatarComDadosInformados()
    {
        $avatar = new Avatar('any', 'image/jpeg');

        $this->assertEquals('any', $avatar->image());
        $this->assertEquals('YW55', $avatar->imageBase64());
        $this->assertEquals('image/jpeg', $avatar->mimeType());
        $this->assertEquals('<img src="data:image/jpeg;base64,YW55" ></img>', $avatar->toHtml());
    }

    /** @test */
    public function deveGerarHtmlComAtributos()
    {
        $avatar = new Avatar('any', 'image/jpeg');
        $html = $avatar->toHtml(['id' => 'my-id', 'width'=> 15]);
        $this->assertEquals('<img src="data:image/jpeg;base64,YW55" id="my-id" width="15"></img>', $html);
    }
}
