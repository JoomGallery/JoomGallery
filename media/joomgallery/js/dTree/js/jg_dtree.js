/*--------------------------------------------------|
| dTree 2.05 | www.destroydrop.com/javascript/tree/ |
|---------------------------------------------------|
| Copyright (c) 2002-2003 Geir Landr?               |
|                                                   |
| This script can be used freely as long as all     |
| copyright messages are intact.                    |
|                                                   |
| Updated: 17.04.2003                               |
|---------------------------------------------------|
|  Base path to image folder added by Andrew Eddie  |
|  17 March 2005                                    |
|---------------------------------------------------|
|  24.02.2009 use icons fromm famfamfam.com added   |
|             by Erftralle                          |       
|  24.02.2009 added functionality for locked nodes  |
|             by Erftralle                          |
|  24.02.2009 minor changes by Erftralle            |
|  04.06.2009 change of CSS class names             |
|             dtree to jg_dtree and                 |
|             dTreeNode to jg_dTreeNode by          |
|             by Erftralle                          |
|  06.06.2009 change of class name dTree            |
|             to jg_dTree by Erftralle              |
|  06.06.2009 change of function name Node to       |
|             to jg_Node by Erftralle               |
*/

// Node object
function jg_Node(id, pid, name, url, locked, title, target, icon, iconOpen, open) {
  this.id       = id;
  this.pid      = pid;
  this.name     = name;
  this.url      = url;
  this.title    = title;
  this.target   = target;
  this.icon     = icon;
  this.iconOpen = iconOpen;
  // added 24.02.2009
  this.locked   = locked;
  this._io      = open || false;
  this._is      = false;
  this._ls      = false;
  this._hc      = false;
  this._ai      = 0;
  this._p;
};

// Tree object
function jg_dTree(objName, basePath) {
  if (!basePath) {
    basePath = 'img/';
  }
  this.config = {
    target         : null,
    folderLinks    : true,
    useSelection   : true,
    useCookies     : true,
    useLines       : true,
    useIcons       : true,
    useStatusText  : false,
    closeSameLevel : false,
    inOrder        : false
  }
  // changed 24.02.2009
  this.icon = {
    root              : basePath+'root.png',
    folder            : basePath+'folder.png',
    folderOpen        : basePath+'folder_open.png',
    node              : basePath+'node.png',
    empty             : basePath+'empty.png',
    line              : basePath+'line.png',
    join              : basePath+'join.png',
    joinBottom        : basePath+'join_bottom.png',
    plus              : basePath+'plus_line.png',
    plusBottom        : basePath+'plus_line_bottom.png',
    minus             : basePath+'minus_line.png',
    minusBottom       : basePath+'minus_line_bottom.png',
    nlPlus            : basePath+'plus.png',
    nlMinus           : basePath+'minus.png',
    folderLocked      : basePath+'folder_locked.png',
    folderOpenLocked  : basePath+'folder_open_locked.png',
    nodeLocked        : basePath+'node_locked.png'
  };
  this.obj           = objName;
  this.aNodes        = [];
  this.aIndent       = [];
  this.root          = new jg_Node(-1);
  this.selectedNode  = null;
  this.selectedFound = false;
  this.completed     = false;
};

// Adds a new node to the node array
jg_dTree.prototype.add = function(id, pid, name, url, locked, title, target, icon, iconOpen, open) {
  // addition by Andrew Eddie, allows id=-1 for auto-indexing
  if (id < 0) {
    id = this.aNodes.length;
  }
  this.aNodes[this.aNodes.length] = new jg_Node(id, pid, name, url, locked, title, target, icon, iconOpen, open);
};

// Open/close all nodes
jg_dTree.prototype.openAll = function() {
  this.oAll(true);
};

jg_dTree.prototype.closeAll = function() {
  this.oAll(false);
}; // Outputs the tree to the page

jg_dTree.prototype.toString = function() {
  var str = '\n<div class="jg_dtree">\n';
  if (document.getElementById) {
    if (this.config.useCookies) this.selectedNode = this.getSelected();
    str += this.addNode(this.root);
  } else str += 'Browser not supported.';
  str += '</div>\n';
  if (!this.selectedFound) this.selectedNode = null;
  this.completed = true;
  return str;
};

// Creates the tree structure
jg_dTree.prototype.addNode = function(pNode) {
  var str = '';
  var n=0;
  if (this.config.inOrder) n = pNode._ai;
  for (n; n<this.aNodes.length; n++) {
    if (this.aNodes[n].pid == pNode.id) {
      var cn = this.aNodes[n];
      cn._p = pNode;
      cn._ai = n;
      this.setCS(cn);
      if (!cn.target && this.config.target) cn.target = this.config.target;
      if (cn._hc && !cn._io && this.config.useCookies) cn._io = this.isOpen(cn.id);
      if (!this.config.folderLinks && cn._hc) cn.url = null;
      if (this.config.useSelection && cn.id == this.selectedNode && !this.selectedFound) {
        cn._is = true;
        this.selectedNode = n;
        this.selectedFound = true;
      }
      str += this.node(cn, n);
      if (cn._ls) break;
    }
  }
  return str;
};

// Creates the node icon, url and text
jg_dTree.prototype.node = function(node, nodeId) {
  var str = '<div class="jg_dTreeNode">\n' + this.indent(node, nodeId);
  if (this.config.useIcons) {
    if (!node.icon) node.icon = (this.root.id == node.pid) ? this.icon.root : ((node._hc) ? this.icon.folder : this.icon.node);
    if (!node.iconOpen) node.iconOpen = (node._hc) ? this.icon.folderOpen : this.icon.node;
    if (this.root.id == node.pid) {
      node.icon = this.icon.root;
      node.iconOpen = this.icon.root;
    }
    // added 24.02.2009
    if( node.locked == true ) {
      node.icon = ((node._hc) ? this.icon.folderLocked : this.icon.nodeLocked);
      node.iconOpen = ((node._hc) ? this.icon.folderOpenLocked : this.icon.nodeLocked); 
    }
    str += '<img id="i' + this.obj + nodeId + '" src="' + ((node._io) ? node.iconOpen : node.icon) + '" alt="" />\n';
  }
  if (node.url) {
    str += '<a id="s' + this.obj + nodeId + '" class="' + ((this.config.useSelection) ? ((node._is ? 'nodeSel' : 'node')) : 'node') + '" href="' + node.url + '"';
    if (node.title) str += ' title="' + node.title + '"';
    if (node.target) str += ' target="' + node.target + '"';
    if (this.config.useStatusText) str += ' onmouseover="window.status=\'' + node.name + '\';return true;" onmouseout="window.status=\'\';return true;" ';
    if (this.config.useSelection && ((node._hc && this.config.folderLinks) || !node._hc)) str += ' onclick="javascript: ' + this.obj + '.s(' + nodeId + ');"';
    str += '>';
  } else if ((!this.config.folderLinks || !node.url) && node._hc && node.pid != this.root.id) {
      // commented 24.02.2009
      // str += '<a href="javascript: ' + this.obj + '.o(' + nodeId + ');" class="node">';
  }
  else {
    // added 24.02.2009
    str += '<span class="node">\n'
  }
  str += node.name;
  if (node.url || ((!this.config.folderLinks || !node.url) && node._hc)) {
     str += '</a>\n';
  }
  else {
    // added 24.02.2009
    str += '</span>\n';
  }
  str += '</div>\n';
  if (node._hc) {
    str += '<div id="d' + this.obj + nodeId + '" class="clip" style="display:' + ((this.root.id == node.pid || node._io) ? 'block' : 'none') + ';">\n';
    str += this.addNode(node);
    str += '</div>\n';
  }
  this.aIndent.pop();
  return str;
};

// Adds the empty and line icons
jg_dTree.prototype.indent = function(node, nodeId) {
  var str = '';
  if (this.root.id != node.pid) {
    for (var n=0; n<this.aIndent.length; n++) str += '<img src="' + ( (this.aIndent[n] == 1 && this.config.useLines) ? this.icon.line : this.icon.empty ) + '" alt="" />';
    (node._ls) ? this.aIndent.push(0) : this.aIndent.push(1);
    if (node._hc) {
      str += '<a href="javascript: ' + this.obj + '.o(' + nodeId + ');"><img id="j' + this.obj + nodeId + '" src="';
      if (!this.config.useLines) str += (node._io) ? this.icon.nlMinus : this.icon.nlPlus;
        else str += ( (node._io) ? ((node._ls && this.config.useLines) ? this.icon.minusBottom : this.icon.minus) : ((node._ls && this.config.useLines) ? this.icon.plusBottom : this.icon.plus ) );
      str += '" alt="" /></a>';
    } else str += '<img src="' + ( (this.config.useLines) ? ((node._ls) ? this.icon.joinBottom : this.icon.join ) : this.icon.empty) + '" alt="" />';
  }
  return str;
};

// Checks if a node has any children and if it is the last sibling
jg_dTree.prototype.setCS = function(node) {
  var lastId;
  for (var n=0; n<this.aNodes.length; n++) {
    if (this.aNodes[n].pid == node.id) node._hc = true;
    if (this.aNodes[n].pid == node.pid) lastId = this.aNodes[n].id;
  }
  if (lastId==node.id) node._ls = true;
};

// Returns the selected node
jg_dTree.prototype.getSelected = function() {
  var sn = this.getCookie('cs' + this.obj);
  return (sn) ? sn : null;
};

// Highlights the selected node
jg_dTree.prototype.s = function(id) {
  if (!this.config.useSelection) return;
  var cn = this.aNodes[id];
  if (cn._hc && !this.config.folderLinks) return;
  if (this.selectedNode != id) {
    if (this.selectedNode || this.selectedNode==0) {
      eOld = document.getElementById("s" + this.obj + this.selectedNode);
      eOld.className = "node";
    }
    eNew = document.getElementById("s" + this.obj + id);
    eNew.className = "nodeSel";
    this.selectedNode = id;
    if (this.config.useCookies) this.setCookie('cs' + this.obj, cn.id);
  }
};

// Toggle Open or close
jg_dTree.prototype.o = function(id) {
  var cn = this.aNodes[id];
  this.nodeStatus(!cn._io, id, cn._ls);
  cn._io = !cn._io;
  if (this.config.closeSameLevel) this.closeLevel(cn);
  if (this.config.useCookies) this.updateCookie();
};

// Open or close all nodes
jg_dTree.prototype.oAll = function(status) {
  for (var n=0; n<this.aNodes.length; n++) {
    if (this.aNodes[n]._hc && this.aNodes[n].pid != this.root.id) {
      this.nodeStatus(status, n, this.aNodes[n]._ls);
      this.aNodes[n]._io = status;
    }
  }
  if (this.config.useCookies) this.updateCookie();
};

// Opens the tree to a specific node
jg_dTree.prototype.openTo = function(nId, bSelect, bFirst) {
  if (!bFirst) {
    for (var n=0; n<this.aNodes.length; n++) {
      if (this.aNodes[n].id == nId) {
        nId=n;
        break;
      }
    }
  }
  var cn=this.aNodes[nId];
  if (cn.pid==this.root.id || !cn._p) return;
  cn._io = true;  cn._is = bSelect;
  if (this.completed && cn._hc) this.nodeStatus(true, cn._ai, cn._ls);
  if (this.completed && bSelect) this.s(cn._ai);
  else if (bSelect) this._sn=cn._ai;
  this.openTo(cn._p._ai, false, true);
};

// Closes all nodes on the same level as certain node
jg_dTree.prototype.closeLevel = function(node) {
  for (var n=0; n<this.aNodes.length; n++) {
    if (this.aNodes[n].pid == node.pid && this.aNodes[n].id != node.id && this.aNodes[n]._hc) {
      this.nodeStatus(false, n, this.aNodes[n]._ls);
      this.aNodes[n]._io = false;
      this.closeAllChildren(this.aNodes[n]);
    }
  }
};

// Closes all children of a node
jg_dTree.prototype.closeAllChildren = function(node) {
  for (var n=0; n<this.aNodes.length; n++) {
    if (this.aNodes[n].pid == node.id && this.aNodes[n]._hc) {
      if (this.aNodes[n]._io) this.nodeStatus(false, n, this.aNodes[n]._ls);
      this.aNodes[n]._io = false;
      this.closeAllChildren(this.aNodes[n]);
    }
  }
};

// Change the status of a node(open or closed)
jg_dTree.prototype.nodeStatus = function(status, id, bottom) {
  eDiv  = document.getElementById('d' + this.obj + id);
  eJoin  = document.getElementById('j' + this.obj + id);
  if (this.config.useIcons) {
    eIcon  = document.getElementById('i' + this.obj + id);
    eIcon.src = (status) ? this.aNodes[id].iconOpen : this.aNodes[id].icon;
  }
  eJoin.src = (this.config.useLines)?
    ((status)?((bottom)?this.icon.minusBottom:this.icon.minus):((bottom)?this.icon.plusBottom:this.icon.plus)):
    ((status)?this.icon.nlMinus:this.icon.nlPlus);
  eDiv.style.display = (status) ? 'block': 'none';
};

// [Cookie] Clears a cookie
jg_dTree.prototype.clearCookie = function() {
  var now = new Date();
  var yesterday = new Date(now.getTime() - 1000 * 60 * 60 * 24);
  this.setCookie('co'+this.obj, 'cookieValue', yesterday);
  this.setCookie('cs'+this.obj, 'cookieValue', yesterday);
};

// [Cookie] Sets value in a cookie
jg_dTree.prototype.setCookie = function(cookieName, cookieValue, expires, path, domain, secure) {
  document.cookie =    escape(cookieName) + '=' + escape(cookieValue)
    + (expires ? '; expires=' + expires.toGMTString() : '')
    + (path ? '; path=' + path : '')
    + (domain ? '; domain=' + domain : '')
    + (secure ? '; secure' : '');
};

// [Cookie] Gets a value from a cookie
jg_dTree.prototype.getCookie = function(cookieName) {
  var cookieValue = '';
  var posName = document.cookie.indexOf(escape(cookieName) + '=');
  if (posName != -1) {
    var posValue = posName + (escape(cookieName) + '=').length;
    var endPos = document.cookie.indexOf(';', posValue);
    if (endPos != -1) cookieValue = unescape(document.cookie.substring(posValue, endPos));
    else cookieValue = unescape(document.cookie.substring(posValue));
  }
  return (cookieValue);
};

// [Cookie] Returns ids of open nodes as a string
jg_dTree.prototype.updateCookie = function() {
  var str = '';
  for (var n=0; n<this.aNodes.length; n++) {
    if (this.aNodes[n]._io && this.aNodes[n].pid != this.root.id) {
      if (str) str += '.';
      str += this.aNodes[n].id;
    }
  }
  this.setCookie('co' + this.obj, str);
};

// [Cookie] Checks if a node id is in a cookie
jg_dTree.prototype.isOpen = function(id) {
  var aOpen = this.getCookie('co' + this.obj).split('.');
  for (var n=0; n<aOpen.length; n++)
    if (aOpen[n] == id) return true;
  return false;
};

jg_dTree.prototype.getNodeByName = function(nName) {
  var nId = 0;
  for (var n=0; n<this.aNodes.length; n++) {
    if (this.aNodes[n].name == nName) {
      nId=n;
      break;
    }
  }
  return nId;
};

jg_dTree.prototype.getNodeByTitle = function(nTitle) {
  var nId = 0;
  for (var n=0; n<this.aNodes.length; n++) {
    if (this.aNodes[n].title == nTitle) {
      nId=n;
      break;
    }
  }
  return nId;
};

// If Push and pop is not implemented by the browser
if (!Array.prototype.push) {
  Array.prototype.push = function array_push() {
    for(var i=0;i<arguments.length;i++)
      this[this.length]=arguments[i];
    return this.length;
  }
};

if (!Array.prototype.pop) {
  Array.prototype.pop = function array_pop() {
    lastElement = this[this.length-1];
    this.length = Math.max(this.length-1,0);
    return lastElement;
  }
};
