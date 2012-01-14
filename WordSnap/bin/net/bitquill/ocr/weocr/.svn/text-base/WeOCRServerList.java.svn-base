/**
 * Copyright 2009 Spiros Papadimitriou <spapadim@cs.cmu.edu>
 * 
 * This file is part of WordSnap OCR.
 * 
 * WordSnap is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * WordSnap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with WordSnap.  If not, see <http://www.gnu.org/licenses/>.
 */
package net.bitquill.ocr.weocr;

import java.io.IOException;
import java.util.ArrayList;

import net.bitquill.ocr.R;

import org.xmlpull.v1.XmlPullParser;
import org.xmlpull.v1.XmlPullParserException;

import android.content.Context;
import android.content.res.XmlResourceParser;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.BaseAdapter;
import android.widget.ListAdapter;
import android.widget.TextView;

public class WeOCRServerList {
    
    private ArrayList<Server> mServerList;
    
    private static class ServerListAdapter extends BaseAdapter {
        
        private ArrayList<Server> mServerList;
        private LayoutInflater mInflater;
        
        private ServerListAdapter (Context context, ArrayList<Server> serverList) {
            mServerList = serverList;
            mInflater = (LayoutInflater)context.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        }

        @Override
        public int getCount() {
            return mServerList.size();
        }

        @Override
        public Server getItem(int position) {
            return mServerList.get(position);
        }

        @Override
        public long getItemId(int position) {
            return position;
        }

        @Override
        public View getView(int position, View convertView, ViewGroup parent) {
            View view;
            if (convertView == null) {
                view = mInflater.inflate(R.layout.server_list_item, parent, false);
            } else {
                view = convertView;
            }

            Server srv = getItem(position);
            TextView titleText = (TextView)view.findViewById(R.id.server_description_text);
            titleText.setText(srv.title);
            TextView tagsText = (TextView)view.findViewById(R.id.server_url_text);
            tagsText.setText(srv.url);
            return view;
        }

    }
    
    public static class Server {
        public String title;
        public String organization;
        public String url;
        public String endpoint;
        public String engine;
        public long mtime;
        public String[] languages;
        
        public Server () { }
        
        public void setLanguages (String langcodes) {
            languages = langcodes.split(":");
        }
        
        public boolean supportsLanguage (String langcode) {
            if (languages == null) {
                return false;
            }
            for (String l : languages) {
                if (l.equals(langcode)) {
                    return true;
                }
            }
            return false;
        }
    }
    
    private static final String SERVER_TAG = "server";
    private static final String TITLE_TAG = "title";
    private static final String ORGANIZATION_TAG = "organization";
    private static final String URL_TAG = "url";
    private static final String ENDPOINT_TAG = "cgi";
    private static final String ENGINE_TAG = "engine";
    private static final String MTIME_TAG = "mtime";
    private static final String LANGCODES_TAG = "langcodes";
    
    public WeOCRServerList (Context context, int xmlResId) throws IOException, XmlPullParserException {
        mServerList = new ArrayList<Server>();
        XmlResourceParser parser = context.getResources().getXml(xmlResId);
        Server srv = null;
        for (int eventType = parser.getEventType(); 
                 eventType != XmlPullParser.END_DOCUMENT; 
                 eventType = parser.next()) {
            String tagName = null;
            switch (eventType) {
            case XmlPullParser.START_TAG:
                tagName = parser.getName();
                if (SERVER_TAG.equals(tagName)) {
                    if (srv != null) {
                        throw new XmlPullParserException("Unexpected start of server element");
                    }
                    srv = new Server();
                } else if (TITLE_TAG.equals(tagName)) {
                    srv.title = parser.nextText();
                } else if (ORGANIZATION_TAG.equals(tagName)) {
                    srv.organization = parser.nextText();
                } else if (URL_TAG.equals(tagName)) {
                    srv.url = parser.nextText();
                } else if (ENDPOINT_TAG.equals(tagName)) {
                    srv.endpoint = parser.nextText();
                } else if (ENGINE_TAG.equals(tagName)) {
                    srv.engine = parser.nextText();
                } else if (MTIME_TAG.equals(tagName)) {
                    srv.mtime = Long.parseLong(parser.nextText());
                } else if (LANGCODES_TAG.equals(tagName)) {
                    srv.setLanguages(parser.nextText());
                }
                break;
            case XmlPullParser.END_TAG:
                tagName = parser.getName();
                if (SERVER_TAG.equals(tagName)) {
                    mServerList.add(srv);
                    srv = null;
                }
                break;
            }
        }
        parser.close();
    }
    
    protected ArrayList<Server> getServerList () {
        return mServerList;
    }
    
    public ListAdapter getServerListAdapter (Context context) {
        return new ServerListAdapter(context, mServerList);
    }
}
