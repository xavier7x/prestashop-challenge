{assign var=_counter value=0}
{function name="menu" nodes=[] depth=0 parent=null}
  {if $nodes|count}
    <ul {if $depth === 0}class="menu-top h-100" id="top-menu" role="navigation"{else} class="menu-sub__list menu-sub__list--{$depth}"{/if} data-depth="{$depth}">
      {foreach from=$nodes item=node}
        {if $node.children|count}
          {assign var=_expand_id value=10|mt_rand:100000}
        {/if}
        <li class="h-100 menu__item--{$depth} {$node.type} menu__item {if $depth === 0}menu__item--top{else}menu__item--sub{/if}{if $node.current} menu__item--current{/if}"
          id="{$node.page_identifier}" {if $node.children|count}aria-haspopup="true" aria-expanded="false"
          aria-owns="top_sub_menu_{$_expand_id}" aria-controls="top_sub_menu_{$_expand_id}"{/if}>
          {assign var=_counter value=$_counter+1}

          {if $node.children|count}
          <div class="menu__item-header">
          {/if}
            <a
              class="d-md-flex w-100 h-100 {if $depth === 0}menu__item-link--top{else}menu__item-link--sub menu__item-link--{$depth}{/if} {if $node.children|count}menu__item-link--hassubmenu{else}menu__item-link--nosubmenu{/if}"
              href="{$node.url}" data-depth="{$depth}"
              {if $node.open_in_new_window} target="_blank" {/if}
            >
              <span class="align-self-center">{$node.label}</span>
            </a>
            {if $node.children|count}
            {* Cannot use page identifier as we can have the same page several times *}
            {assign var=_expand_id value=10|mt_rand:100000}
            <span class="visible--mobile">
                <span data-target="#top_sub_menu_{$_expand_id}" data-toggle="collapse"
                      class="d-block navbar-toggler icon-collapse">
                  <i class="material-icons menu__collapseicon">&#xE313;</i>
                </span>
              </span>
          </div>
          {/if}
          {if $node.children|count}
            <div class="{if $depth === 0}menu-sub {/if}clearfix collapse show" data-collapse-hide-mobile
                 id="top_sub_menu_{$_expand_id}" role="group" aria-labelledby="{$node.page_identifier}"
                 aria-expanded="false" aria-hidden="true">
              <div{if $depth === 0} class="menu-sub__content"{/if}>
                {menu nodes=$node.children depth=$node.depth parent=$node}
              </div>
            </div>
          {/if}
        </li>
      {/foreach}
    </ul>
  {/if}
{/function}
<nav class="menu visible--mobile" id="_desktop_top_menu">
  {menu nodes=$menu.children}
</nav>
<div class="hamburger-menu visible--desktop">
    <div class="hamburger-icon">
        <div class="bar"></div>
        <div class="bar"></div>
        <div class="bar"></div>
    </div>
    <span class="visible--desktop texto_plano">Todas las categorias</span>
    <nav class="menu visible--desktop" id="hamburger-menu _desktop_top_menu">
        {menu nodes=$menu.children}
    </nav>
</div>
<style>
.hamburger-menu {
    display: flex;
    align-items: center;
    cursor: pointer;
    position: relative;
    z-index:69656;
}

.hamburger-icon {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    width: 30px;
    height: 30px;
    padding: 5px;
    box-sizing: border-box;
}

.bar {
    width: 100%;
    height: 3px;
    background-color: #333;
    padding-top: 1px;
}

.menu {
    display: none;
    flex-direction: column;
    background-color: #fff;
    position: absolute;
    top: 100%;
    left: 0;
    width: 100%;
    z-index: 1;
    transition: opacity 0.3s ease-in-out;
}

.hamburger-menu.open .menu {
    display: flex;
    opacity: 1;
}

</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var hamburgerIcon = document.querySelector('.hamburger-icon');
    var hamburgerContainer = document.querySelector('.hamburger-menu');

    hamburgerIcon.addEventListener('click', function () {
        hamburgerContainer.classList.toggle('open');
    });

    // Cerrar el menú al hacer clic fuera del mismo
    window.addEventListener('click', function (event) {
        if (!hamburgerContainer.contains(event.target)) {
            hamburgerContainer.classList.remove('open');
        }
    });
});

</script>
