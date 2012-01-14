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
package net.bitquill.ocr.image;

public interface StructuringElement {

    public int getWidth ();
    public int getHeight ();
    
    public int getMinX ();
    public int getMaxX ();
    public int getMinY ();
    public int getMaxY ();
    
    public int getNumNeighbors ();
    public int[] getHorizontalOffsets ();
    public int[] getVerticalOffsets ();
    
    public int[] getLinearOffsets (int imgWidth, int imgHeight);
}
